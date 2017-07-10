PROJECT_PATH=$(pwd)

PROTO_PATH=$PROJECT_PATH/Service
PHP_OUT=$PROJECT_PATH/Service
GRPC_OUT=$PROJECT_PATH/Service

GRPC_PLUGIN_PATH=/Users/lidanyang/Software/lib/grpc/bins/opt/grpc_php_plugin
PROTOC_PATH=/usr/local/protobuf/bin/protoc


for file in $PROTO_PATH/*
do
    if [ "${file##*.}" = "proto" ];
    then
        FILE_LIST=${FILE_LIST}" ${file}";
    fi
done

$PROTOC_PATH --proto_path=$PROTO_PATH --php_out=$PHP_OUT --grpc_out=$GRPC_OUT --plugin=protoc-gen-grpc=$GRPC_PLUGIN_PATH $FILE_LIST