<?php
if( isset($_POST['file']) && file_exists($_POST['file'])) {
    if(isset($_POST['token']) && !empty($_POST['token'])){
        require_once('sys/server/tables.php');
        require_once('sys/db.php');
        $token_exist = $db->where('session_id', $_POST['token'])->get(T_SESSIONS,1,array('*'));
        if($token_exist){
            require_once("sys/import3p/spaces-api-master/spaces.php");
            $data    = array();
            $configs = $db->get(T_CONFIG,null,array('name','value'));
            foreach ($configs as $key => $config) {
                $data[$config->name] = $config->value;
            }
            if( $data['digital_ocean'] == '0' ){
                exit();
            }
            $key = $data['digital_ocean_key'];
            $secret = $data['digital_ocean_s_key'];
            $space_name = $data['digital_ocean_space_name'];
            $region = $data['digital_ocean_region'];
            $space = new SpacesConnect($key, $secret, $space_name, $region);
            $path_to_file = $_POST['file'];
            $space->UploadFile($path_to_file, "public");
        }
    }
}
exit();