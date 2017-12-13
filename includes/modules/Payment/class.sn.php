<?php
/*
Plugin Name: Hostbill - sn
Plugin URI: http://sn
Description: Hostbill - sn
Version: 1.0
Author: sn
*/

class sn extends PaymentModule 
{
    protected $modname = 'درگاه پرداخت آنلاین';

    protected $description = 'ماژول درگاه پرداخت آنلاین';

    protected $supportedCurrencies = array();


    protected $configuration = array(
        'api_sn' => array(
            'value' 	  => '',
            'type'		  => 'input',
            'description' => 'شناسه درگاه خود را وارد نمایید .'
        ),
		'success_message' => array(
            'value'       => 'پرداخت با موفقیت انجام شد.',
            'type'        => 'input',
            'description' => 'پیام موفقیت'
        ),
    );

    public function drawForm() 
	{		
		$api_sn = $this->configuration['api_sn']['value'];
		$amount = str_replace('.', '', $this->amount);
		// Security
@session_start();
$sec = uniqid();
$md = md5($sec.'vm');
// Security
		$callback = $this->callback_url.'&invoiceid='.$this->invoice_id.'&amount='.$amount.'&am='.$this->amount.'&md='.$md.'&sec='.$sec;

	$data_string = json_encode(array(
'pin'=> $api_sn,
'price'=> $amount,
'callback'=> $callback ,
'order_id'=> $this->invoice_id,
'description' => 'پرداخت فاکتور شماره: '.$this->invoice,
'ip'=> $_SERVER['REMOTE_ADDR'],
'callback_type'=>2
));

$ch = curl_init('https://developerapi.net/api/v1/request');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Content-Type: application/json',
'Content-Length: ' . strlen($data_string))
);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
$result = curl_exec($ch);
curl_close($ch);


$json = json_decode($result,true); 
            
           $res =  $json['result'];

                    switch ($res) {
            
                            case -1:
                            $msg = "پارامترهای ارسالی برای متد مورد نظر ناقص یا خالی هستند . پارمترهای اجباری باید ارسال گردد";
                            break;
                             case -2:
                            $msg = "دسترسی api برای شما مسدود است";
                            break;
                             case -6:
                            $msg = "عدم توانایی اتصال به گیت وی بانک از سمت وبسرویس";
                            break;

                             case -9:
                            $msg = "خطای ناشناخته";
                            break;

                             case -20:
                            $msg = "پین نامعتبر";
                            break;
                             case -21:
                            $msg = "ip نامعتبر";
                            break;

                             case -22:
                            $msg = "مبلغ وارد شده کمتر از حداقل مجاز میباشد";
                            break;


                            case -23:
                            $msg = "مبلغ وارد شده بیشتر از حداکثر مبلغ مجاز هست";
                            break;
                            
                              case -24:
                            $msg = "مبلغ وارد شده نامعتبر";
                            break;
                            
                              case -26:
                            $msg = "درگاه غیرفعال است";
                            break;
                            
                              case -27:
                            $msg = "آی پی مسدود شده است";
                            break;
                            
                              case -28:
                            $msg = "آدرس کال بک نامعتبر است ، احتمال مغایرت با آدرس ثبت شده";
                            break;
                            
                              case -29:
                            $msg = "آدرس کال بک خالی یا نامعتبر است";
                            break;
                            
                              case -30:
                            $msg = "چنین تراکنشی یافت نشد";
                            break;
                            
                              case -31:
                            $msg = "تراکنش ناموفق است";
                            break;
                            
                              case -32:
                            $msg = "مغایرت مبالغ اعلام شده با مبلغ تراکنش";
                            break;
                         
                            
                              case -35:
                            $msg = "شناسه فاکتور اعلامی order_id نامعتبر است";
                            break;
                            
                              case -36:
                            $msg = "پارامترهای برگشتی بانک bank_return نامعتبر است";
                            break;
                                case -38:
                            $msg = "تراکنش برای چندمین بار وریفای شده است";
                            break;
                            
                              case -39:
                            $msg = "تراکنش در حال انجام است";
                            break;
                            
                            case 1:
                            $msg = "پرداخت با موفقیت انجام گردید.";
                            break;

                            default:
                               $msg = $json['msg'];
                        }

if(!empty($json['result']) AND $json['result'] == 1)
{
		// Set Session
$_SESSION[$sec] = [
	'price'=>$amount ,
	'order_id'=>$invoice_id ,
	'au'=>$json['au'] ,
];
			echo ('<div style="display:none">'.$json['form'].'</div>Please wait ... <script language="javascript">document.payment.submit(); </script>');
			exit;	
		}
		else
		{
			$message = $msg;
		}
	}
	

    function callback() 
	{  
		$api_sn = $this->configuration['api_sn']['value'];
				if(isset($_GET['sec']) or isset($_GET['md']) AND $mdback == $mdurl )
				{
		session_start();
// Security
$sec=$_GET['sec'];
$mdback = md5($sec.'vm');
$mdurl=$_GET['md'];
// Security	
$transData = $_SESSION[$sec];
$au=$transData['au']; //
$amount=$transData['price']; //
$invoice_id = $_GET['order_id'];
$bank_return = $_POST + $_GET ;
$data_string = json_encode(array (
	
'pin' => $api_sn,
'price' => $amount,
'order_id' => $invoice_id,
'au' => $au,
'bank_return' =>$bank_return,
));

$ch = curl_init('https://developerapi.net/api/v1/verify');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Content-Type: application/json',
'Content-Length: ' . strlen($data_string))
);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
$result = curl_exec($ch);
curl_close($ch);
$json = json_decode($result,true);

                    if($json['result'] == 1)
					{
                            //2. log incoming payment
                            $this->logActivity(array(
                                'result' => 'Successfull',
                                'output' => $_POST
                            ));

                            //3. add transaction to invoice
                            
                            $amount = $transData['price'];
                            $fee = 0;
                            $transaction_id = $au;
                            
                            $this->addTransaction(array(
                                'in' => $amount,
                                'invoice_id' => $invoice_id,
                                'fee' => $fee,
                                'transaction_id' => $transaction_id
                		      
                            ));
                			$this->addInfo($this->configuration['success_message']['value']);
                            Utilities::redirect('?cmd=clientarea');
                    } 
		              else 
            		      {
                         $this->logActivity(array(
                            'result' => 'Failed',
                            'output' => $_POST
            				
                        ));
            			Utilities::redirect('?cmd=clientarea');	
                    }
		  } 
		else 
		{
             $this->logActivity(array(
                'result' => 'Failed',
                'output' => $_POST
				
            ));
			Utilities::redirect('?cmd=clientarea');	
        }
    }

}