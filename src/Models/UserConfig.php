<?php
namespace MgpLabs\SmartAdmin\Models;


class UserConfig extends SmartAdminBaseModel
{
    protected $rels = [
        'user_id' => [
            'model' => 'User',
            'pluck' => "CONCAT(name,' ',surname) AS name"
        ]
    ];

    protected $fillable = [
        'user_id',
        'configs'
    ];

    protected $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'configs' => 'required'
    ];

    public function user(){
        return parent::belongsTo($this->modelName('User'));
    }

    /**
     * Get user Configs
     *
     * @param $user_id
     * @return array
     */
    public static function getConfigs($user_id){
        $configs = self::where('user_id', $user_id)->select('configs')->first();

        return $configs ? $configs['configs'] : '{}';
    }

    /**
     * Check if exists user config, override it,
     * else create new config row
     *
     * @param $user
     * @param $config
     * @return bool
     */
    public static function createOrUpdate($user, $config){
        $user_config = static::where('user_id', $user)->first();
        $saved = false;

        if($user_config){
            $user_config->configs = $config;
            if($user_config->save()){
                $saved = true;
            }
        }else{
            $to_save = [
                'user_id' => $user,
                'configs' => $config
            ];
            if(static::create($to_save)){
                $saved = true;
            }
        }

        return $saved;
    }
}
