<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;

class HttpsqsController extends Controller
{
	public $queue_master;
	public $queue_slave;
    
    public function options($actionID)
    {
		return ['queue_master', 'queue_slave'];
    }
    
    public function optionAliases()
    {
		return ['m' => 'queue_master', 's' => 'queue_slave'];
    }
    
    public function actionIndex()
    {
		echo $this->queue_master."\n";
		echo $this->queue_slave."\n";
    }
	
	public function actionHttpsqsMasterDaemon()
    {
		// $result = Yii::$app->httpsqs->publishMessage('wuwei', '测试信息A');
		// print_r($result);exit();
		// $result = Yii::$app->httpsqs->getMessage('wuwei');
		// print_r($result);exit();
		
	    while(true) {  
		  //$result = $httpsqs->gets($name);
		  //$result = $httpsqs->gets("queue_A");
		    $result_m = Yii::$app->httpsqs->getsMessage($this->queue_master);
		    if($result_m != false){
			    $pos = $result_m["pos"]; //当前队列消息的读取位置点  
			    $data = $result_m["data"]; //当前队列消息的内容  
			    if ($data != "HTTPSQS_GET_END" && $data != "HTTPSQS_ERROR") {  
					//...去做应用操作...
				
					//应用操作异常,1、记录pos点队列日志 2、将失败日志入队列B
					//$result_b = $httpsqs->put("queue_B", $data);
					$result_s = Yii::$app->httpsqs->publishMessage($this->queue_slave, $data);
					if($result_s == false){
						//将队列记入日志，并管理员处理
					}
				} else {  
					sleep(1); //暂停1秒钟后，再次循环  
				}
			}
		}
		
    }
	
	public function actionHttpsqsSlaveDaemon()
    {
		while(true) {
			$result_s = Yii::$app->httpsqs->getsMessage($this->queue_slave);
		    if($result_s != false){
				$pos = $result_s["pos"]; //当前队列消息的读取位置点  
				$data = $result_s["data"]; //当前队列消息的内容  
				if ($data != "HTTPSQS_GET_END" && $data != "HTTPSQS_ERROR") {  
					//...去做应用操作...

					//应用操作异常,1、将失败消息发送给管理员处理
			    } else {  
					sleep(1); //暂停1秒钟后，再次循环  
			    }
		  }
		}
	}
	
}
