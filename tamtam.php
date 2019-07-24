<?php
class tamtam{
    function __construct($token,$user_id)
    {
        $this->token = $token;
        $this->user_id = $user_id;
    }
    public function bot($chat,$meh,$data){
        $url = "https://botapi.tamtam.chat/".$meh."?access_token=".$this->token."&chat_id=".$chat;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
            return $res;
    }
    public function sendaction($chat,$action){
        $url = "https://botapi.tamtam.chat/chats/{$chat}/actions?access_token=".$this->token;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,'{
"action":"'.$action.'"
}');
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
            return $res;
    }
    function fileget($get_url,$from_id,$ext='mp4'){
        $url = trim($get_url);
        $file_name = pathinfo($get_url, PATHINFO_FILENAME);
            $file = fopen($url,"rb");
            $directory = "data/".$this->user_id."/upload/";
            $valid_exts = array("exe","apk","zip","rar","jpeg","gif","png","doc","docx","jpg","html","mp4","asp","xml","JPEG","bmp"); 
            if(!isset($ext)){
                $ext = end(explode(".",strtolower(basename($url))));
            }
            if(in_array($ext,$valid_exts)){
                $rand = rand(1000,9999);
                $filename = "$rand.$ext";
                $newfile = fopen($directory . $filename, "wb");
                if($newfile){
                    while(!feof($file)){
                        fwrite($newfile,fread($file,1024 * 8),1024 * 8);
                    }
                    return "data/".$this->user_id."/upload/".$filename;
                }else{
                    return 'File does not exists';
                }
            }else{
                return 'Invalid URL';
            }
        }
    function geturl($type){
        $header = array(
            'POST /uploads?access_token='.$this->token.'&type='.$type.' HTTP/1.1',
            'Host: botapi.tamtam.chat',
            'Accept: */*',
            'User-Agent: Mozilla/5.0 (compatible; Rigor/1.0.0; http://rigor.com)',
            'Content-Length: 3',
            'Content-Type: application/x-www-form-urlencoded');
        $url = "https://botapi.tamtam.chat/uploads?access_token=".$this->token."&type=".$type;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,'ddd');
        $res = curl_exec($ch);
        //echo $res['url'];
        return $res;
    }
    function updoc($type,$post){
        $header = array('Content-Type: multipart/form-data');
        $file =json_decode($this->geturl($type),true);
        $url = $file['url'];
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,array('data='=>new \CURLFile($post)));
        $res = curl_exec($ch);
        ///sleep(1);
        preg_match('/"token":"(.*?)"}/m',$res,$re);
        $contents = str_replace(array("\r\n", "\n\r", "\r", "\n"), '', $re[1]);
         file_put_contents('vid.json',$contents);
        return $contents;
    }
    public function sendmessage($chat,$text){
        $this->sendaction($chat,'typing_on');
        return $this->bot($chat,'messages',[
            'text'=>$text
            ]);
        $this->sendaction($chat,'typing_off');
    }
    public function sendphoto($chat,$text,$photo,$key=[]){
        $this->sendaction($chat,'sending_photo');
        return $this->bot($chat,'messages',[
            'text'=>$text,
            'attachments'=>[
                [
                    'type'=>'image',
                    'payload'=>[
                        'url'=>$photo
                        ]
                ]
                ]
            ]);
    }
    public function sendvideo($chat,$text,$video){
        $this->sendaction($chat,'sending_video');
        $vid = $this->updoc('video',$this->fileget($video,$chat));
        $vs = rand(1,2);
        sleep(3);
        return $this->bot($chat,'messages',[
            'text'=>$text,
            'attachments'=>[
                [
                    'type'=>'video',
                    'payload'=>[
                        'token'=>$vid
                        ]
                    ]
                ]
            ]);
            unlink($vid);
    }
    public function sendfile($chat,$text,$video){
        $ext = end(explode(".",strtolower(basename($video))));
        $vid = $this->updoc('file',$this->fileget($video,$chat,$ext));
        //sleep(1);
        return $this->bot($chat,'messages',[
            'text'=>$text,
            'attachments'=>[
                [
                    'type'=>'file',
                    'payload'=>[
                        'token'=>$vid
                        ]
                ]
                ]
            ]);
        unlink($vid);    
    }
    public function sendaudio($chat,$text,$video){
        $this->sendaction($chat,'sending_audio');
        $ext = end(explode(".",strtolower(basename($video))));
        $vid = $this->updoc('audio',$this->fileget($video,$chat,$ext));
        //sleep(1);
        return $this->bot($chat,'messages',[
            'text'=>$text,
            'attachments'=>[
                [
                    'type'=>'audio',
                    'payload'=>[
                        'token'=>$vid
                        ]
                    ]
                ]
            ]);
        unlink($vid);    
    }
    
}
