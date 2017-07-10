<?php

namespace base\framework\mysql;

use core\component\config\Config;
use PDO;

class DB {
    private static $instance = null;

    /**
     * @return DB
     */
    public static function getInstance()
    {
        if(DB::$instance == null)
        {
            DB::$instance = new DB(Config::get('db'));
        }
        return DB::$instance;
    }

    /**
     * @var \PDOStatement PDO操作实例
     */
    protected $PDOStatement = null;
    // 当前操作所属的模型名
    protected $model      = '_think_';
    // 当前SQL指令
    protected $queryStr   = '';
    protected $modelSql   = array();
    // 最后插入ID
    protected $lastInsID  = null;
    // 返回或者影响记录数
    protected $numRows    = 0;
    // 事务指令数
    protected $transTimes = 0;
    // 错误信息
    protected $error      = '';
    /**
     * @var PDO 数据库连接ID 支持多个连接
     */
    protected $linkID     = null;
    // 数据库连接参数配置
    protected $config     = array(
        'dsn'               =>  '', //
        'charset'           =>  'utf8',      // 数据库编码默认采用utf8
        'prefix'            =>  '',    // 数据库表前缀
        'release'           =>  false, // 数据库调试模式
    );
    // 数据库表达式
    protected $exp = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN','not in'=>'NOT IN','between'=>'BETWEEN','not between'=>'NOT BETWEEN','notbetween'=>'NOT BETWEEN');
    // 查询表达式
    protected $selectSql  = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%LOCK%%COMMENT%';
    // 查询次数
    protected $queryTimes   =   0;
    // 执行次数
    protected $executeTimes =   0;
    // PDO连接参数
    protected $options = array(
        PDO::ATTR_CASE              =>  PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           =>  PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      =>  PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES =>  false,
    );
    protected $bind         =   array(); // 参数绑定

    /**
     * 架构函数 读取数据库配置信息
     * @access public
     * @param array $config 数据库配置数组
     */
    public function __construct($config=[]){
        if(!empty($config)) {
            $this->config   =   array_merge($this->config,$config);
        }
    }

    /**
     * 连接数据库方法
     * @access public
     * @param string $config
     * @param bool $autoConnection
     * @return mixed
     * @throws \Exception
     */
    public function connect($config='', $autoConnection=false) {
        if (empty($this->linkID)) {
            if(empty($config))  $config =   $this->config;
            var_dump($config);
            try{
                if(empty($config['dsn'])) {
                    $config['dsn']  =   $this->parseDsn($config);
                }
                $this->linkID = new PDO( $config['dsn'], $config['user'], $config['password'],$this->options);
            }catch (\PDOException $e) {
                if($autoConnection){
                    return $this->connect([], false);
                }elseif($config['release']){
                    throw new \Exception($e->getMessage());
                }
            }
        }
        return $this->linkID;
    }

    /**
     * 解析pdo连接的dsn信息
     * @access public
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config){
        $dsn  =   'mysql:dbname='.$config['database'].';host='.$config['host'];
        if(!empty($config['host'])) {
            $dsn  .= ';port='.$config['port'];
        }elseif(!empty($config['socket'])){
            $dsn  .= ';unix_socket='.$config['socket'];
        }

        if(!empty($config['charset'])){
            //为兼容各版本PHP,用两种方式设置编码
            $this->options[\PDO::MYSQL_ATTR_INIT_COMMAND]    =   'SET NAMES '.$config['charset'];
            $dsn  .= ';charset='.$config['charset'];
        }
        return $dsn;
    }

    /**
     * 释放查询结果
     * @access public
     */
    public function free() {
        $this->PDOStatement = null;
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL
     * @return mixed
     */
    public function query($str,$fetchSql=false) {
        $this->initConnect();
        if ( !$this->linkID ) return false;
        $this->queryStr     =   $str;
        if(!empty($this->bind)){
            $that   =   $this;
            $this->queryStr =   strtr($this->queryStr,array_map(function($val) use($that){ return '\''.$that->escapeString($val).'\''; },$this->bind));
        }
        if($fetchSql){
            return $this->queryStr;
        }
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        $this->queryTimes++;

        // 调试开始
        $this->debug(true);
        try{
            $this->PDOStatement = $this->linkID->prepare($str);
            if(false === $this->PDOStatement){
                $this->error();
                return false;
            }
            foreach ($this->bind as $key => $val) {
                if(is_array($val)){
                    $this->PDOStatement->bindValue($key, $val[0], $val[1]);
                }else{
                    $this->PDOStatement->bindValue($key, $val);
                }
            }
            $this->bind =   array();

            $result =   $this->PDOStatement->execute();
            // 调试结束
            $this->debug(false);
            if ( false === $result ) {
                $this->error();
                return false;
            } else {
                return $this->getResult();
            }
        }catch (\PDOException $e) {
            if ($e->getCode() == 'HY000') {
                $this->linkID = $this->connect();
                return $this->query($str, $fetchSql);
            } else {
                $this->error();
                return false;
            }
        }
    }

    /**
     * 执行语句
     * @access public
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL
     * @return mixed
     */
    public function execute($str,$fetchSql=false) {
        $this->initConnect();
        if ( !$this->linkID ) return false;
        $this->queryStr = $str;
        if(!empty($this->bind)){
            $that   =   $this;
            $this->queryStr =   strtr($this->queryStr,array_map(function($val) use($that){ return '\''.$that->escapeString($val).'\''; },$this->bind));
        }
        if($fetchSql){
            return $this->queryStr;
        }
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        $this->executeTimes++;

        // 记录开始执行时间
        $this->debug(true);
        try{
            $this->PDOStatement =   $this->linkID->prepare($str);
            if(false === $this->PDOStatement) {
                $this->error();
                return false;
            }
            foreach ($this->bind as $key => $val) {
                if(is_array($val)){
                    $this->PDOStatement->bindValue($key, $val[0], $val[1]);
                }else{
                    $this->PDOStatement->bindValue($key, $val);
                }
            }
            $this->bind =   array();

            $result =   $this->PDOStatement->execute();
            // 调试结束
            $this->debug(false);
            if ( false === $result) {
                $this->error();
                return false;
            } else {
                $this->numRows = $this->PDOStatement->rowCount();
                if(preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
                    $this->lastInsID = $this->linkID->lastInsertId();
                }
                return $this->numRows;
            }
        }catch (\PDOException $e) {
            if ($e->getCode() == 'HY000') {
                $this->linkID = $this->connect();
                return $this->execute($str, $fetchSql);
            } else {
                $this->error();
                return false;
            }
        }
    }

    /**
     * 启动事务
     * @access public
     * @return bool
     */
    public function startTrans() {
        $this->initConnect();
        if ( !$this->linkID ) return false;
        //数据rollback 支持
        if ($this->transTimes == 0) {
            $this->linkID->beginTransaction();
        }
        $this->transTimes++;
        return true;
    }

    /**
     * 用于非自动提交状态下面的查询提交
     * @access public
     * @return boolean
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = $this->linkID->commit();
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = $this->linkID->rollback();
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * 获得所有的查询数据
     * @access private
     * @return array
     */
    private function getResult() {
        //返回数据集
        $result =   $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
        $this->numRows = count( $result );
        return $result;
    }

    /**
     * 获得查询次数
     * @access public
     * @param boolean $execute 是否包含所有查询
     * @return integer
     */
    public function getQueryTimes($execute=false){
        return $execute?$this->queryTimes+$this->executeTimes:$this->queryTimes;
    }

    /**
     * 获得执行次数
     * @access public
     * @return integer
     */
    public function getExecuteTimes(){
        return $this->executeTimes;
    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        $this->linkID = null;
    }

    /**
     * 数据库错误信息
     * 并显示当前的SQL语句
     * @access public
     * @return string
     * @throws \Exception
     */
    public function error() {
        if($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $this->error = $error[1].':'.$error[2];
        }else{
            $this->error = '';
        }
        if('' != $this->queryStr){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        // 记录错误日志
        //trace($this->error,'','ERR');
        if($this->config['release']) {// 开启数据库调试模式
            throw new \Exception($this->error);
        }else{
            return $this->error;
        }
    }

    /**
     * 设置锁机制
     * @access protected
     * @param bool $lock
     * @return string
     */
    protected function parseLock($lock=false) {
        return $lock?   ' FOR UPDATE '  :   '';
    }

    /**
     * set分析
     * @access protected
     * @param array $data
     * @return string
     */
    protected function parseSet($data) {
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $set[]  =   $this->parseKey($key).'='.$val[1];
            }elseif(is_null($val)){
                $set[]  =   $this->parseKey($key).'=NULL';
            }elseif(is_scalar($val)) {// 过滤非标量数据
                if(0===strpos($val,':') && in_array($val,array_keys($this->bind)) ){
                    $set[]  =   $this->parseKey($key).'='.$this->escapeString($val);
                }else{
                    $name   =   count($this->bind);
                    $set[]  =   $this->parseKey($key).'=:'.$name;
                    $this->bindParam($name,$val);
                }
            }
        }
        if (empty($set))
        {
            return '';
        }
        return ' SET '.implode(',',$set);
    }

    /**
     * 参数绑定
     * @access protected
     * @param string $name 绑定参数名
     * @param mixed $value 绑定值
     * @return void
     */
    protected function bindParam($name,$value){
        $this->bind[':'.$name]  =   $value;
    }

    /**
     * 字段名分析
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        $key   =  trim($key);
        if(!is_numeric($key) && !preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
            $key = '`'.$key.'`';
        }
        return $key;
    }

    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value =  strpos($value,':') === 0 && in_array($value,array_keys($this->bind))? $this->escapeString($value) : '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }

    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return string
     */
    protected function parseField($fields) {
        if(is_string($fields) && '' !== $fields) {
            $fields    = explode(',',$fields);
        }
        if(is_array($fields)) {
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array   =  array();
            foreach ($fields as $key=>$field){
                if(!is_numeric($key))
                    $array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
                else
                    $array[] =  $this->parseKey($field);
            }
            $fieldsStr = implode(',', $array);
        }else{
            $fieldsStr = '*';
        }
        //TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
        return $fieldsStr;
    }

    /**
     * table分析
     * @access protected
     * @param mixed $tables
     * @return string
     */
    protected function parseTable($tables) {
        if(is_array($tables)) {// 支持别名定义
            $array   =  array();
            foreach ($tables as $table=>$alias){
                if(!is_numeric($table))
                    $array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
                else
                    $array[] =  $this->parseKey($alias);
            }
            $tables  =  $array;
        }elseif(is_string($tables)){
            $tables  =  explode(',',$tables);
            array_walk($tables, array(&$this, 'parseKey'));
        }
        return implode(',',$tables);
    }

    /**
     * where分析
     * @access protected
     * @param mixed $where
     * @return string
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        }else{ // 使用数组表达式
            $operate  = isset($where['_logic'])?strtoupper($where['_logic']):'';
            if(in_array($operate,array('AND','OR','XOR'))){
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate    =   ' '.$operate.' ';
                unset($where['_logic']);
            }else{
                // 默认进行 AND 运算
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                if(is_numeric($key)){
                    $key  = '_complex';
                }
                if(0===strpos($key,'_')) {
                    // 解析特殊条件表达式
                    $whereStr   .= $this->parseThinkWhere($key,$val);
                }else{
                    // 查询字段的安全过滤
                    // if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
                    //     E(L('_EXPRESS_ERROR_').':'.$key);
                    // }
                    // 多条件支持
                    $multi  = is_array($val) &&  isset($val['_multi']);
                    $key    = trim($key);
                    if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array =  explode('|',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = $this->parseWhereItem($this->parseKey($k),$v);
                        }
                        $whereStr .= '( '.implode(' OR ',$str).' )';
                    }elseif(strpos($key,'&')){
                        $array =  explode('&',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= '( '.implode(' AND ',$str).' )';
                    }else{
                        $whereStr .= $this->parseWhereItem($this->parseKey($key),$val);
                    }
                }
                $whereStr .= $operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }

    // where子单元分析
    protected function parseWhereItem($key,$val) {
        $whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                $exp	=	strtolower($val[0]);
                if(preg_match('/^(eq|neq|gt|egt|lt|elt)$/',$exp)) { // 比较运算
                    $whereStr .= $key.' '.$this->exp[$exp].' '.$this->parseValue($val[1]);
                }elseif(preg_match('/^(notlike|like)$/',$exp)){// 模糊查找
                    if(is_array($val[1])) {
                        $likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
                        if(in_array($likeLogic,array('AND','OR','XOR'))){
                            $like       =   array();
                            foreach ($val[1] as $item){
                                $like[] = $key.' '.$this->exp[$exp].' '.$this->parseValue($item);
                            }
                            $whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';
                        }
                    }else{
                        $whereStr .= $key.' '.$this->exp[$exp].' '.$this->parseValue($val[1]);
                    }
                }elseif('bind' == $exp ){ // 使用表达式
                    $whereStr .= $key.' = :'.$val[1];
                }elseif('exp' == $exp ){ // 使用表达式
                    $whereStr .= $key.' '.$val[1];
                }elseif(preg_match('/^(notin|not in|in)$/',$exp)){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.$this->exp[$exp].' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                            $val[1] =  explode(',',$val[1]);
                        }
                        $zone      =   explode(',',$this->parseValue($val[1]));
                        $whereStr .= $key.' '.$this->exp[$exp].' ('.$zone.')';
                    }
                }elseif(preg_match('/^(notbetween|not between|between)$/',$exp)){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  $key.' '.$this->exp[$exp].' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]);
                }else{
                    //E(L('_EXPRESS_ERROR_').':'.$val[0]);
                }
            }else {
                $count = count($val);
                $rule  = isset($val[$count-1]) ? (is_array($val[$count-1]) ? strtoupper($val[$count-1][0]) : strtoupper($val[$count-1]) ) : '' ;
                if(in_array($rule,array('AND','OR','XOR'))) {
                    $count  = $count -1;
                }else{
                    $rule   = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= $key.' '.$data.' '.$rule.' ';
                    }else{
                        $whereStr .= $this->parseWhereItem($key,$val[$i]).' '.$rule.' ';
                    }
                }
                $whereStr = '( '.substr($whereStr,0,-4).' )';
            }
        }else {
            $whereStr .= $key.' = '.$this->parseValue($val);
        }
        return $whereStr;
    }

    /**
     * 特殊条件分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    protected function parseThinkWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr = substr($this->parseWhere($val),6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array   =  array();
                foreach ($where as $field=>$data)
                    $array[] = $this->parseKey($field).' = '.$this->parseValue($data);
                $whereStr   = implode($op,$array);
                break;
        }
        return '( '.$whereStr.' )';
    }

    /**
     * limit分析
     * @access protected
     * @param mixed $limit
     * @return string
     */
    protected function parseLimit($limit) {
        return !empty($limit)?   ' LIMIT '.$limit.' ':'';
    }

    /**
     * join分析
     * @access protected
     * @param mixed $join
     * @return string
     */
    protected function parseJoin($join) {
        $joinStr = '';
        if(!empty($join)) {
            $joinStr    =   ' '.implode(' ',$join).' ';
        }
        return $joinStr;
    }

    /**
     * order分析
     * @access protected
     * @param mixed $order
     * @return string
     */
    protected function parseOrder($order) {
        if(is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val){
                if(is_numeric($key)) {
                    $array[] =  $this->parseKey($val);
                }else{
                    $array[] =  $this->parseKey($key).' '.$val;
                }
            }
            $order   =  implode(',',$array);
        }
        return !empty($order)?  ' ORDER BY '.$order:'';
    }

    /**
     * group分析
     * @access protected
     * @param mixed $group
     * @return string
     */
    protected function parseGroup($group) {
        return !empty($group)? ' GROUP BY '.$group:'';
    }

    /**
     * having分析
     * @access protected
     * @param string $having
     * @return string
     */
    protected function parseHaving($having) {
        return  !empty($having)?   ' HAVING '.$having:'';
    }

    /**
     * comment分析
     * @access protected
     * @param string $comment
     * @return string
     */
    protected function parseComment($comment) {
        return  !empty($comment)?   ' /* '.$comment.' */':'';
    }

    /**
     * distinct分析
     * @access protected
     * @param mixed $distinct
     * @return string
     */
    protected function parseDistinct($distinct) {
        return !empty($distinct)?   ' DISTINCT ' :'';
    }

    /**
     * union分析
     * @access protected
     * @param mixed $union
     * @return string
     */
    protected function parseUnion($union) {
        if(empty($union)) return '';
        if(isset($union['_all'])) {
            $str  =   'UNION ALL ';
            unset($union['_all']);
        }else{
            $str  =   'UNION ';
        }
        foreach ($union as $u){
            $sql[] = $str.(is_array($u)?$this->buildSelectSql($u):$u);
        }
        if(empty($sql))
        {
            return '';
        }
        return implode(' ',$sql);
    }

    /**
     * 参数绑定分析
     * @access protected
     * @param array $bind
     */
    protected function parseBind($bind){
        $this->bind   =   array_merge($this->bind,$bind);
    }

    /**
     * index分析，可在操作链中指定需要强制使用的索引
     * @access protected
     * @param mixed $index
     * @return string
     */
    protected function parseForce($index) {
        if(empty($index)) return '';
        if(is_array($index)) $index = join(",", $index);
        return sprintf(" FORCE INDEX ( %s ) ", $index);
    }

    /**
     * ON DUPLICATE KEY UPDATE 分析
     * @access protected
     * @param mixed $duplicate
     * @return string
     */
    protected function parseDuplicate($duplicate){
        // 布尔值或空则返回空字符串
        if(is_bool($duplicate) || empty($duplicate)) return '';

        if(is_string($duplicate)){
            // field1,field2 转数组
            $duplicate = explode(',', $duplicate);
        }elseif(is_object($duplicate)){
            // 对象转数组
            $duplicate = get_class_vars($duplicate);
        }
        $updates                    = array();
        foreach((array) $duplicate as $key=>$val){
            if(is_numeric($key)){ // array('field1', 'field2', 'field3') 解析为 ON DUPLICATE KEY UPDATE field1=VALUES(field1), field2=VALUES(field2), field3=VALUES(field3)
                $updates[]          = $this->parseKey($val)."=VALUES(".$this->parseKey($val).")";
            }else{
                if(is_scalar($val)) // 兼容标量传值方式
                    $val            = array('value', $val);
                if(!isset($val[1])) continue;
                switch($val[0]){
                    case 'exp': // 表达式
                        $updates[]  = $this->parseKey($key)."=($val[1])";
                        break;
                    case 'value': // 值
                    default:
                        $name       = count($this->bind);
                        $updates[]  = $this->parseKey($key)."=:".$name;
                        $this->bindParam($name, $val[1]);
                        break;
                }
            }
        }
        if(empty($updates)) return '';
        return " ON DUPLICATE KEY UPDATE ".join(', ', $updates);
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insert($data,$options=array(),$replace=false) {
        $values  =  $fields    = array();
        $this->model  =   $options['model'];
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $fields[]   =  $this->parseKey($key);
                $values[]   =  $val[1];
            }elseif(is_null($val)){
                $fields[]   =   $this->parseKey($key);
                $values[]   =   'NULL';
            }elseif(is_scalar($val)) { // 过滤非标量数据
                $fields[]   =   $this->parseKey($key);
                if(0===strpos($val,':') && in_array($val,array_keys($this->bind))){
                    $values[]   =   $this->parseValue($val);
                }else{
                    $name       =   count($this->bind);
                    $values[]   =   ':'.$name;
                    $this->bindParam($name,$val);
                }
            }
        }
        // 兼容数字传入方式
        $replace= (is_numeric($replace) && $replace>0)?true:$replace;
        $sql    = (true===$replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')'.$this->parseDuplicate($replace);
        $sql    .= $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
    }


    /**
     * 批量插入记录
     * @access public
     * @param mixed $dataSet 数据集
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insertAll($dataSet,$options=array(),$replace=false) {
        $values  =  array();
        $this->model  =   $options['model'];
        if(!is_array($dataSet[0])) return false;
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        $fields =   array_map(array($this,'parseKey'),array_keys($dataSet[0]));
        foreach ($dataSet as $data){
            $value   =  array();
            foreach ($data as $key=>$val){
                if(is_array($val) && 'exp' == $val[0]){
                    $value[]   =  $val[1];
                }elseif(is_null($val)){
                    $value[]   =   'NULL';
                }elseif(is_scalar($val)){
                    if(0===strpos($val,':') && in_array($val,array_keys($this->bind))){
                        $value[]   =   $this->parseValue($val);
                    }else{
                        $name       =   count($this->bind);
                        $value[]   =   ':'.$name;
                        $this->bindParam($name,$val);
                    }
                }
            }
            $values[]    = '('.implode(',', $value).')';
        }
        // 兼容数字传入方式
        $replace= (is_numeric($replace) && $replace>0)?true:$replace;
        $sql    =  (true===$replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES '.implode(',',$values).$this->parseDuplicate($replace);
        $sql    .= $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 通过Select方式插入记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options  查询数据参数
     * @return false | integer
     */
    public function selectInsert($fields,$table,$options=array()) {
        $this->model  =   $options['model'];
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        if(is_string($fields))   $fields    = explode(',',$fields);
        array_walk($fields, array($this, 'parseKey'));
        $sql   =    'INSERT INTO '.$this->parseTable($table).'('.implode(',', $fields) . ')';
        $sql   .= $this->buildSelectSql($options);
        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return false | integer
     */
    public function update($data,$options) {
        $this->model  =   $options['model'];
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        $table  =   $this->parseTable($options['table']);
        $sql   = 'UPDATE ' . $table . $this->parseSet($data);
        if(strpos($table,',')){// 多表更新支持JOIN操作
            $sql .= $this->parseJoin(!empty($options['join'])?$options['join']:'');
        }
        $sql .= $this->parseWhere(!empty($options['where'])?$options['where']:'');
        if(!strpos($table,',')){
            //  单表更新支持order和lmit
            $sql   .=  $this->parseOrder(!empty($options['order'])?$options['order']:'')
                .$this->parseLimit(!empty($options['limit'])?$options['limit']:'');
        }
        $sql .=   $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 删除记录
     * @access public
     * @param array $options 表达式
     * @return false | integer
     */
    public function delete($options=array()) {
        $this->model  =   $options['model'];
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        $table  =   $this->parseTable($options['table']);
        $sql    =   'DELETE FROM '.$table;
        if(strpos($table,',')){// 多表删除支持USING和JOIN操作
            if(!empty($options['using'])){
                $sql .= ' USING '.$this->parseTable($options['using']).' ';
            }
            $sql .= $this->parseJoin(!empty($options['join'])?$options['join']:'');
        }
        $sql .= $this->parseWhere(!empty($options['where'])?$options['where']:'');
        if(!strpos($table,',')){
            // 单表删除支持order和limit
            $sql .= $this->parseOrder(!empty($options['order'])?$options['order']:'')
                .$this->parseLimit(!empty($options['limit'])?$options['limit']:'');
        }
        $sql .=   $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return mixed
     */
    public function select($options=array()) {
        $this->model  =   $options['model'];
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        $sql    = $this->buildSelectSql($options);
        $result   = $this->query($sql,!empty($options['fetch_sql']) ? true : false);
        return $result;
    }

    /**
     * 生成查询SQL
     * @access public
     * @param array $options 表达式
     * @return string
     */
    public function buildSelectSql($options=array()) {
        if(isset($options['page'])) {
            // 根据页数计算limit
            list($page,$listRows)   =   $options['page'];
            $page    =  $page>0 ? $page : 1;
            $listRows=  $listRows>0 ? $listRows : (is_numeric($options['limit'])?$options['limit']:20);
            $offset  =  $listRows*($page-1);
            $options['limit'] =  $offset.','.$listRows;
        }
        $sql  =   $this->parseSql($this->selectSql,$options);
        return $sql;
    }

    /**
     * 替换SQL语句中表达式
     * @access public
     * @param $sql
     * @param array $options 表达式
     * @return string
     */
    public function parseSql($sql,$options=array()){
        $sql   = str_replace(
            array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%LOCK%','%COMMENT%','%FORCE%'),
            array(
                $this->parseTable($options['table']),
                $this->parseDistinct(isset($options['distinct'])?$options['distinct']:false),
                $this->parseField(!empty($options['field'])?$options['field']:'*'),
                $this->parseJoin(!empty($options['join'])?$options['join']:''),
                $this->parseWhere(!empty($options['where'])?$options['where']:''),
                $this->parseGroup(!empty($options['group'])?$options['group']:''),
                $this->parseHaving(!empty($options['having'])?$options['having']:''),
                $this->parseOrder(!empty($options['order'])?$options['order']:''),
                $this->parseLimit(!empty($options['limit'])?$options['limit']:''),
                $this->parseUnion(!empty($options['union'])?$options['union']:''),
                $this->parseLock(isset($options['lock'])?$options['lock']:false),
                $this->parseComment(!empty($options['comment'])?$options['comment']:''),
                $this->parseForce(!empty($options['force'])?$options['force']:'')
            ),$sql);
        return $sql;
    }

    /**
     * 获取最近一次查询的sql语句
     * @param string $model  模型名
     * @access public
     * @return string
     */
    public function getLastSql($model='') {
        return $model?$this->modelSql[$model]:$this->queryStr;
    }

    /**
     * 获取最近插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID() {
        return $this->lastInsID;
    }

    /**
     * 获取最近的错误信息
     * @access public
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {
        return addslashes($str);
    }

    /**
     * 设置当前操作模型
     * @access public
     * @param string $model  模型名
     * @return void
     */
    public function setModel($model){
        $this->model =  $model;
    }

    /**
     * 数据库调试 记录当前SQL
     * @access protected
     * @param boolean $start  调试开始标记 true 开始 false 结束
     */
    protected function debug($start) {
        if($this->config['release']) {// 开启数据库调试模式
            if($start) {

            }else{
                $this->modelSql[$this->model]   =  $this->queryStr;
                //$this->model  =   '_think_';
                //trace($this->queryStr.' [ RunTime:'.G('queryStartTime','queryEndTime').'s ]','','SQL');
            }
        }
    }

    /**
     * 初始化数据库连接
     * @access protected
     * @return void
     * @throws \Exception
     */
    protected function initConnect() {
        if ( !$this->linkID ) {
            $this->linkID = $this->connect();
            var_dump($this->linkID);
        }
        try {
            $this->linkID->getAttribute(\PDO::ATTR_SERVER_INFO);
        } catch (\Exception $e) {
            if ($e->getCode() == 'HY000') {
                $this->linkID = $this->connect();
            } else {
                throw $e;
            }
        }
    }

    /**
     * 取得数据表的字段信息
     * @access public
     * @param $tableName
     * @return array
     */
    public function getFields($tableName) {
        $this->initConnect();
        list($tableName) = explode(' ', $tableName);
        if(strpos($tableName,'.')){
            list($dbName,$tableName) = explode('.',$tableName);
            $sql   = 'SHOW COLUMNS FROM `'.$dbName.'`.`'.$tableName.'`';
        }else{
            $sql   = 'SHOW COLUMNS FROM `'.$tableName.'`';
        }

        $result = $this->query($sql);
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                if(\PDO::CASE_LOWER != $this->linkID->getAttribute(\PDO::ATTR_CASE)){
                    $val = array_change_key_case ( $val ,  CASE_LOWER );
                }
                $info[$val['field']] = array(
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => (bool) ($val['null'] === ''), // not null is empty, null is yes
                    'default' => $val['default'],
                    'primary' => (strtolower($val['key']) == 'pri'),
                    'autoinc' => (strtolower($val['extra']) == 'auto_increment'),
                );
            }
        }
        return $info;
    }

    /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        // 释放查询
        if ($this->PDOStatement){
            $this->free();
        }
        // 关闭连接
        $this->close();
    }
}
