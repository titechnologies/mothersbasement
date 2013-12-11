<?php

class FBShop_Platform_Model_Platform extends Mage_Core_Model_Abstract
{ 
 public $userApi;
 public $pswApi;
 public $instaled;
 public $version;
 public $host;
 public $hostMage;
    public function _construct()
    {
        parent::_construct();
        $this->_init('platform/platform');
        $this->host="http://www.shopidoo.com";
   $this->instaled=0;
   $this->version='0.3.5';
   $this->hostMage= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
 // $this->execute();

    }
    public function loadUserApi(){
        
       try{
          $user1 = new Mage_Api_Model_User();
                $user1['email']     = 'ShopPlatform@mail.com';
    
              // $this->psw=$result['api_key'];
                 $users = Mage::getResourceModel('api/user_collection')->getAllIds();
                 $p=0;
                 foreach ($users as $userId)
                 {
                     $user=Mage::getModel('api/user')->load($userId);
                     if   ($user1['email']==$user->getEmail()){
                         $p=1; break;
                         
                         } ;
                 }
                  if ($p==1){
                 $this->userApi=$user->getUsername();
                 $this->pswApi="dev12345";
                 $this->instaled=1;
                 //return true ;
                 }
                  } catch (Execption $e){
          return false;
      }
                 
    }
    public function generateUserApi(){
           try{
        $user = new Mage_Api_Model_User();
                $user1['email']     = 'ShopPlatform@mail.com';
                 
               
              // $this->psw=$result['api_key'];
                 $users = Mage::getResourceModel('api/user_collection')->getAllIds();
                 $p=0;
                 foreach ($users as $userId)
                 {
                     $user=Mage::getModel('api/user')->load($userId);
                     if   ($user1['email']==$user->getEmail()){
                         $user->delete();
                                             
                         } ;
                 } 
                 $this->execute();
                  } catch (Execption $e){
          return false;
      }
   }
   public function execute(){
          try{
   
          if (!$this->isInstaledUserApi()){
         $this->createUserApi();
          }
           } catch (Execption $e){
          return false;
      }
   }
   
   public function isInstaledUserApi(){
           try{
                $user1 = new Mage_Api_Model_User();
                $user1['email']     = 'ShopPlatform@mail.com';
                 
               
              // $this->psw=$result['api_key'];
                 $users = Mage::getResourceModel('api/user_collection')->getAllIds();
                 $p=0;
                  $user=Mage::getModel('api/user');
                 foreach ($users as $userId)
                 {
                   $user->load($userId);
                     if   ($user1['email']==$user->getEmail()){
                         $p=1; break;
                         
                         } ;
                 }
                 
                 if (($user->getId()!=NULL)&&($p==1)){
                 $this->userApi=$user->getUsername();
                 $this->pswApi="dev12345";
                 $this->instaled=1;
                 return true ;
                 }
                 return  false;
                  } catch (Execption $e){
          return false;
      }
    }
    
    
    public function isInstaledPlugin(){
           try{
    
                $sql = "SELECT platform_id  FROM platform";
               $data = Mage::getSingleton('core/resource') ->getConnection('core_read')->fetchAll($sql);           
            return ( is_array($result) && count($result) > 0 )? true : false;
             } catch (Execption $e){
          return false;
      }
    }
    
    
    public function createUserApi(){
        
       try{
             $user = new Mage_Api_Model_User();
             $this->userApi=$this->guid();
             $this->pswApi="dev12345"    ;
            // echo $this->psw;
                $user['firstname'] = 'FbShop';
                $user['lastname']  = 'ShopPlatform';
                $user['email']     = 'ShopPlatform@mail.com';
                $user['modified']  = Mage::getSingleton('core/date')->gmtDate();
                $user['username']  =  $this->userApi;
                $user['api_key']   = $this->pswApi;
                $user['is_active'] = intval('1');
                 
               $user->save();
               $id=$user->getId();
                $user=Mage::getModel('api/user')->load($id);
               $this->pswApi="dev12345";
               $this->createRoleApi($id);
                } catch (Execption $e){
          return false;
      }
           }
  public  function guid(){
         mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
        return $uuid;
   
}   
      public function createRoleApi($userId){
             try{
      
             $roles = Mage::getResourceModel('api/role_collection')->getAllIds();
                 $p=0;
                 foreach ($roles as $roleId)
                 {
                     $rol=Mage::getModel('api/role')->load($roleId);
                     if   ($rol->getRoleName()=="FbShops"){
                         $rol->delete();
                            
                         } ;
                 } 
          
          
          
          
          $role = new Mage_Api_Model_Role();
           $role->setRoleName("FbShops");
           $role->setParentId(0);
           $role->setTreeLevel(1);
           $role->setUserId(0);
              $role->setRoleType("G");
              $role->save();  
              $id=$role->getId();
              $this->createRule($id);
               $role1 = new Mage_Api_Model_Role();
           $role1->setRoleName("FbShops");
           $role1->setParentId($role->getId());
           $role1->setTreeLevel(2);
           $role1->setUserId($userId);
              $role1->setRoleType("U");
                   $role1->save();
        } catch (Execption $e){
          return false;
      }
                    
    }
    
   public function createRule($roleId){ 
       try{
      $rule =  new Mage_Api_Model_Rules();
      $rule->setResourceId('all');
      $rule->setRoleId($roleId);
      $rule->setAssertId(0);
      // $rule->setPermission('allow');
       $rule->setRoleType('G');
       $rule->setPermission('allow');
       $rule['api_permission']='allow';
       $rule->save();
        } catch (Execption $e){
          return false;
      }
  }
  
  public function loadShop(){
      try{     
      $models = Mage::getResourceModel('platform/platform_collection')->getAllIds();
            if (!empty($models)) {
                $modelId = $models[0];
                $this->load($modelId);
                if ($this->getId() != NULL) {
                    return $this;
                }
            }
      } catch (Execption $e){
          return false;
      }
  }
  function post_request($url, $data, $referer='') {
 
    // Convert the data array into URL Parameters like a=b&foo=bar etc.
    $data = http_build_query($data);
 
    // parse the given URL
    $url = parse_url($url);
 
    if ($url['scheme'] != 'http') { 
        die('Error: Only HTTP request are supported !');
    }
 
    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];
 
    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($host, 80, $errno, $errstr, 30);
 
    if ($fp){
 
        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
 
        if ($referer != '')
            fputs($fp, "Referer: $referer\r\n");
 
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ". strlen($data) ."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);
 
        $result = ''; 
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
    }
    else { 
        return array(
            'status' => 'err', 
            'error' => "$errstr ($errno)"
        );
    }
 
    // close the socket connection:
    fclose($fp);
 
    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
 
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';
 
    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );
}
public function testUrl($url){
    extract($_POST);

//set POST variables
//$url = 'http://domain.com/get-post.php';
$fields = array(
            'lname'=>"a"
       
        );

//url-ify the data for the POST
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string,'&');

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_POST,count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);
    
}
}