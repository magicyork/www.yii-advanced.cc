<?php
namespace common\components;

use yii\base\Component;
use Httpsqs\HttpsqsClient;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\Exception;

/**
 * Class HttpSQS
 * @package common\components
 */
class HttpSQS extends Component
{
    /* private $_host = '127.0.0.1';
    private $_port = 1218;
    private $_auth = '';
    private $_charset = 'utf-8';
    private $_httpsqs = null;
    private $_queue = ''; */
	
	public $_host = '127.0.0.1';
    public $_port = 1218;
    public $_auth = '';
    public $_charset = 'utf-8';
    public $_httpsqs = null;
    public $_queue = '';

    /**
     * 连接
     */
    public function httpsqs(){
        $this->getHttpsqs();
    }

    /**
     * 设置默认 queue
     * @param $queue
     */
    public function setDefaultQueue($queue){
        $this->_queue = $queue;

    }

    /**
     * @return bool
     */
    private function _isHttpsqs(){
        if($this->_httpsqs){
            return true;
        }
        return false;
    }

    /**
     *将文本信息放入一个队列（注意：如果要放入队列的PHP变量是一个数组，需要事先使用序列化、json_encode等函数转换成文本） 
     *如果入队列成功，返回布尔值：true  
     *如果入队列失败，返回布尔值：false
     * 
     * 发布消息
     * @param $queue
     * @param $message
     * @return bool
     */
    public function publishMessage($queue, $message){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->put($newQueue, $message);
        return $result;
    }

    /**
     *从一个队列中取出文本信息 
     *返回该队列的内容 
     *如果没有未被取出的队列，则返回文本信息：HTTPSQS_GET_END 
     *如果发生错误，返回布尔值：false  

     * 拉取消息
     * @param $queue
     * @return bool|string
     */
    public function getMessage($queue){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->get($newQueue);
        return $result;
    }
	
    /**
     *从一个队列中取出文本信息和当前队列读取点Pos 
     *返回数组示例：array("pos" => 7, "data" => "text message") 
     *如果没有未被取出的队列，则返回数组：array("pos" => 0, "data" => "HTTPSQS_GET_END")    如果发生错误，返回布尔值：false 

     * 拉取消息
     * @param $queue
     * @return bool|array
     */
    public function getsMessage($queue){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->gets($newQueue);
        return $result;
    }

    /**
     * 查看队列状态（普通方式）
     * @param $queue
     * @return string 
     */
    public function getStatus($queue){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->status($newQueue);
        return $result;
    }

    /**
     * 查看队列状态（JSON方式）
     *返回示例：{"name":"queue_name","maxqueue":5000000,"putpos":130,"putlap":1,"getpos":120,"getlap":1,"unread":10}
     * @param $queue
     * @return string 
     */
    public function getStatusJson($queue){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->status_json($newQueue);
        return $result;
    }

    /**
     * 查看指定队列位置点的内容 
     * 返回指定队列位置点的内容。
     * @param $queue
     * @param $pos
     * @return string 
     */
    public function getMessageInPos($queue, $pos){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->view($newQueue, $pos);
        return $result;
    }

    /**
     * 重置指定队列 
     * 如果重置队列成功，返回布尔值：true  
     * 如果重置队列失败，返回布尔值：false 
     * @param $queue
     * @return bool 
     */
    public function resetQueue($queue){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->reset($newQueue);
        return $result;
    }

    /**
     * 更改指定队列的最大队列数量 
     * 如果更改成功，返回布尔值：true 
     * 如果更改操作被取消，返回布尔值：false  
     * @param $queue
     * @param $num
     * @return bool 
     */
    public function setMaxQueue($queue, $num){
        $newHttpsqs = $this->getHttpsqs();
        $newQueue = isset($queue)?$queue:$this->_queue;
        $result = $newHttpsqs->maxqueue($newQueue, $num);
        return $result;
    }

    /**
     * 修改定时刷新内存缓冲区内容到磁盘的间隔时间 
     * 如果更改成功，返回布尔值：true 
     * 如果更改操作被取消，返回布尔值：false
     * @param $num
     * @return bool 
     */
    public function setSyncTime($num){
        $newHttpsqs = $this->getHttpsqs();
        $result = $newHttpsqs->synctime($num);
        return $result;
	}

    /**
     * @param $host
     */
    public function setHost($host){
        $this->_host = $host;
    }

    /**
     * @param $port
     */
    public function setPort($port){
        $this->_port = $port;
    }

    /**
     * @param $auth
     */
    public function setAuth($auth){
        $this->_auth = $auth;
    }

    /**
     * @param $charset
     */
    public function setCharset($charset){
        $this->_charset = $charset;
    }

    /**
     * @return null|httpsqs
     * @throws ErrorException
     * @throws \yii\base\ExitException
     */
    public function getHttpsqs(){
        if(!$this->_isHttpsqs()){
            try{
                $this->_httpsqs = new HttpsqsClient($this->_host, $this->_port, $this->_auth, $this->_charset);
            } catch (Exception $e){
                throw new ErrorException('HttpSQS server connect error',500,1);
            }
        }
        return $this->_httpsqs;
    }
}
