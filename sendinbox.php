<?php
/**
 * @Author: Eka Syahwan
 * @Date:   2017-09-14 06:33:28
 * @Last Modified by:   Eka Syahwan
 * @Last Modified time: 2018-04-26 08:45:17
 */
require_once 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'modules/src/Exception.php';
require 'modules/src/PHPMailer.php';
require 'modules/src/SMTP.php';

class Sendinbox extends Config
{
    function __construct()
    {   
        $this->modules          = new SendinboxModules;
        $this->emailValid       = new SmtpEmailValidation;

        if(!function_exists('curl_init')) {
            die('cURL tidak ditemukan ! silahkan install curl');
        }
        
        $template[0] .= "=========================================\r\n";
        $template[0] .= "      _______    || Sendinbox ".$this->modules->versi()."\r\n";
        $template[0] .= "     |==   []|   || (c) ".date(Y)." Bug7sec\r\n";
        $template[0] .= "     |  ==== |   || www.bmarket.or.id\r\n";
        $template[0] .= "     '-------'   ||\r\n";
        $template[0] .= "=========================================\r\n";

        $template[1] .= "======================\r\n";
        $template[1] .= "         __          || Sendinbox ".$this->modules->versi()."\r\n"; 
        $template[1] .= " RECIVED| _] MAILBOX || (c) ".date(Y)." Bug7sec\r\n";
        $template[1] .= "     .--||-----.     || www.bmarket.or.id\r\n";
        $template[1] .= "     |  ||     | (C) ||\r\n";
        $template[1] .= "_____|__||_____|     ||\r\n";
        $template[1] .= "          \ |        ||\r\n";
        $template[1] .= "======================\r\n";

        $template[2] .= "=========================================\r\n";
        $template[2] .= " Time : ".date("h").":".date("m").":".date("s")."\r\n";
        $template[2] .= " .'`~~~~~~~~~~~`'. || spammers' time\r\n";
        $template[2] .= " (  .'11 12 1'.  ) || That second is my result\r\n";
        $template[2] .= " |  :10 \    2:  | || ===================\r\n";
        $template[2] .= " |  :9   @-> 3:  | || Sendinbox ".$this->modules->versi()."\r\n"; 
        $template[2] .= " |  :8       4;  | || (c) ".date(Y)." Bug7sec\r\n";
        $template[2] .= " '. '..7 6 5..' .' || www.bmarket.or.id\r\n";
        $template[2] .= " ~-------------~   ||\r\n";
        $template[2] .= "=========================================\r\n";


        print_r( $template[rand(0,2) ]);
    
        $LoadEmail              = $this->modules->stuck("[ Load Email List (list.txt) ] : "); 
        $du                     = $this->modules->stuck("[ Send Duplicate Mail (0 = Yes , 1 = No)] : "); 
        $Priority               = $this->modules->stuck("[ Priority (1 = High, 2 = Medium, 3 = Low)] : "); 
        //$debug                  = $this->modules->stuck("[ Enable Debug (0 = Yes , 1 = No)] : "); 

        //$du           = 0;
        //$Priority     = 3;
        $debug          = 1;
        $this->cHeader          = $this->CustomHeader();
        $this->listEmail        = $this->modules->load($LoadEmail , $du);
        $this->smtp_config      = null;
        $this->sendinboxpr      = $Priority;
        $this->debug            = str_replace("1", "no", str_replace("0", "yes", $debug));
        $this->run();
    }
    function send(){
        $mail = new PHPMailer(true);
        try {

            $mail->setLanguage('id', 'modules/language/');
            
            if($this->debug == 'yes'){
                $mail->SMTPDebug    = 3;                               
            }else{
                $mail->SMTPDebug    = 0;                               
            }

            $mail->isSMTP();                                            
            $mail->Host             = $this->smtp_config['smtp_host'];       
            $mail->SMTPAuth         = true;                               
            $mail->SMTPKeepAlive    = true;
            $mail->Priority         = $this->sendinboxpr;
            $mail->Username         = $this->smtp_config['smtp_user'];                 
            $mail->Password         = $this->smtp_config['smtp_pass'];                             
            $mail->SMTPSecure       = $this->smtp_config['smtp_secure'];                                
            $mail->Port             = $this->smtp_config['smtp_port'];                                 
            $mail->From             = $this->modules->alias( $this->smtp_config['recipients']['from_email'] , $this->email);
            $mail->FromName         = $this->modules->alias( $this->smtp_config['recipients']['from_name']  , $this->email);
            $mail->AllowEmpty       = true;


            foreach ($this->cHeader as $k => $v) {
                foreach ($v as $headerKey => $headerValue) {
                    if( !empty($headerKey) ){
                        $mail->addCustomHeader($headerKey, $headerValue);
                    }
                }
            }

            foreach ($this->smtp_config[content][attachments] as $key => $attfile) {
                if($attfile != ""){
                    $flocation = 'attachments/'.$attfile;
                    if( file_exists($flocation) ){


                        if(pathinfo($flocation)[extension] == 'html'){
                            $dompdf = new Dompdf();
                            $dompdf->load_html(preg_replace('/>\s+</', '><', file_get_contents( $flocation )));
                            $dompdf->render();
                            file_put_contents('attachments/'.pathinfo($flocation)[filename].'.pdf', $dompdf->output());
                            $mail->addAttachment('attachments/'.pathinfo($flocation)[filename].'.pdf');
                        }

                        if(pathinfo($flocation)[extension] == 'pdf'){
                             $mail->addAttachment('attachments/'.$attfile);
                        }

                    }
                }
            }
            


            $mail->Encoding = 'base64';
            $mail->CharSet  = 'UTF-8';

            $mail->AddAddress($this->email);
            
            $mail->isHTML(true); 
            
            $content = $this->modules->arrayrandom(  $this->smtp_config['content']['format'] );

            if(!file_exists('letter/'.$content['value'])){
                die("[Sendinbox] ============>> Letter Tidak ada <<============\r\n");
            }
            
            $bodyLetter         = $this->modules->alias( file_get_contents('letter/'.$content['value']) , $this->email);
            $mail->Subject      = $this->modules->alias( $content['key'] , $this->email , true);
            $mail->Body         = $this->modules->alias( $bodyLetter , $this->email  );  

            $mail->send();
            
            $this->modules->save('sendinbox-success.txt',$this->email);
            return 'Message has been sent';
        } catch (Exception $e) {
            $this->modules->save('sendinbox-failed.txt',$this->email);
            return $mail->ErrorInfo."\r\n";
        }     
    }
    function run(){
        $hit = 1;
        $num = 1;

        $this->sendinbox_config     = $this->setting();
        $this->smtp_array           = $this->smtp();


        foreach ($this->listEmail['list'] as $key => $email) {
            
            if( $this->sendinbox_config['anti_bounce'] == true){
                if(!preg_match("/hotmail|live|outlook/", $email)){
                    $result         = $this->emailValid->test('admin@localhost',$email);
                    if($result['success'] == 1 || $result['success'] == true){
                        $bounce_status  = true;
                    }else{
                        $bounce_status  = false;
                    }
                }else{
                    $bounce_status  = true;
                }
            }else{
                    $bounce_status  = true;
            }            
            
            
            for ($i=0; $i <1; $i++) { 
                
                $this->smtp_config  = $this->modules->arrayrandom(  $this->smtp_array )['value'];
                if( !empty($email) && !empty($this->smtp_config['smtp_user']) && !empty($this->smtp_config['smtp_host']) && $bounce_status == true){
                    break;
                }
                $i++;
            
            }

            if( !empty($email) && !empty($this->smtp_config['smtp_user']) && !empty($this->smtp_config['smtp_host']) && $bounce_status == true){

                $this->email        = $email; 

                if(count($this->smtp_array) == 0){
                    die("[Sendinbox] ============>> SMTP Tidak ada <<============\r\n");
                }
                
                echo "[Sendinbox][".$hit."/".$this->listEmail['total']."|".count($this->smtp_array)."][".substr($this->smtp_config['smtp_user'], 0,8)."..] ".$email." => ";
                
                $send   = $this->send();
                echo str_replace('Message has been sent', 'success' , $send);
                
                if( $send != 'Message has been sent' ){
                    unset($this->smtp_array[$this->smtp_config['key']]);
                }
                

                if($num == $this->sendinbox_config['number']){
                    sleep($this->sendinbox_config['delay']);
                    $num = 0;
                }
                echo "\r\n";
                $num++;
                $hit++;

            }else{
                echo "[Sendinbox][".$hit."/".$this->listEmail['total']."|".count($this->smtp_array)."][".substr($this->smtp_config['smtp_user'], 0,8)."..] ".$email." => ".($result['msg'] ? $result['msg']:"skip");
                echo "\r\n";
            }
        }

    }
}
$Sendinbox = new Sendinbox;
