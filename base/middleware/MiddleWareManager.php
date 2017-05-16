<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 2017/5/15
 * Time: 下午8:38
 */

namespace base\middleware;


use Iterator;

class MiddleWareManager implements Iterator
{
    private static $instance = null;

    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new MiddleWareManager();
        }
        return self::$instance;
    }

    /**
     * @var array
     */
    private $list = [];

    private $count = 0;

    private $index = 0;

    public function __construct()
    {

    }

    public function add(MiddleWare $middleWare)
    {
        $this->list[$this->count ++] = $middleWare;
    }

    public function remove($index)
    {
        unset($this->list[$index]);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        // TODO: Implement current() method.
        while( !isset($this->list[$this->index]))
        {
            $this->index ++;
        }
        return $this->list[$this->index];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->index ++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return ($this->index < $this->count) && isset($this->list[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->index = 0;
    }
}