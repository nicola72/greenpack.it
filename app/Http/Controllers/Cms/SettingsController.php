<?php

namespace App\Http\Controllers\Cms;

use App\Model\Cms\CmsClearpassword;
use App\Model\Cms\RoleCms;
use App\Model\Cms\UserCms;
use App\Model\Module;
use App\Model\ModuleConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\AssignOp\Mod;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $moduli = Module::all();

        $params = ['moduli'=> $moduli,'title_page' => 'Moduli'];
        return view('cms.settings.index',$params);
    }

    public function create_module()
    {
        $form_name = 'form_create_module';
        $roles = RoleCms::all();
        $moduli = Module::all();

        $params = [
            'form_name' => $form_name,
            'roles'     => $roles,
            'modules'   => $moduli
        ];

        return view('cms.settings.create_module',$params);
    }

    public function store_module(Request $request)
    {
        try{
            $module = new Module();
            $module->role_id = $request->role_id;
            if($request->parent_id != '')
            {
                $module->parent_id = $request->parent_id;
            }
            $module->nome = $request->nome;
            $module->label = $request->label;
            $module->icon = $request->icon;
            $module->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiunto con successo!','url' => '/cms/settings'];
    }

    public function edit_module(Request $request,$id)
    {
        $modulo    = Module::find($id);
        $form_name = 'form_edit_module';
        $roles     = RoleCms::all();
        $moduli    = Module::all();

        $params = [
            'form_name' => $form_name,
            'roles'     => $roles,
            'modules'   => $moduli,
            'modulo'    => $modulo
        ];

        return view('cms.settings.edit_module',$params);
    }

    public function update_module(Request $request,$id)
    {
        $modulo = Module::find($id);

        try{
            $modulo->nome = $request->nome;
            $modulo->icon = $request->icon;
            $modulo->label = $request->label;
            $modulo->role_id = $request->role_id;
            if($request->parent_id == '')
            {
                $modulo->parent_id = null;
            }
            else
            {
                $modulo->parent_id = $request->parent_id;
            }

            $modulo->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!','url' => '/cms/settings'];

    }

    public function destroy_config_module(Request $request,$id)
    {
        $config_module = ModuleConfig::find($id);
        $config_module->delete();
        return back()->with('success','Elemento cancellato!');
    }

    public function config_module(Request $request,$id)
    {
        $modulo = Module::find($id);
        $configs = $modulo->configs;
        $title_page = 'Configurazioni Modulo '.$modulo->nome;

        $params = [
            'modulo' => $modulo,
            'configs' => $configs,
            'title_page' => $title_page
        ];
        return view('cms.settings.config_module',$params);
    }

    public function create_config_module(Request $request, $id)
    {
        $modulo = Module::find($id);

        $params = [
            'modulo'=> $modulo,
            'form_name'=>'form_create_config_module'
        ];
        return view('cms.settings.create_config_module',$params);
    }

    public function create_copy_config_module(Request $request, $id)
    {
        $modules = Module::all()->except($id);
        $modulo = Module::find($id);
        $params = [
            'modulo'    => $modulo,
            'modules'   => $modules,
            'form_name' => 'form_create_copy_config_module'
        ];

        return view('cms.settings.create_copy_config_module',$params);
    }

    public function store_copy_config_module(Request $request)
    {
        $modulo = Module::find($request->module_id);
        $modulo_selected = Module::find($request->id_selected);

        $configs = $modulo_selected->configs;

        foreach ($configs as $config)
        {
            try{
                $config_module = new ModuleConfig();
                $config_module->type = $config->type;
                $config_module->module_id = $modulo->id;
                $config_module->nome = $config->nome;
                $config_module->desc = $config->desc;
                $config_module->value = $config->value;
                $config_module->save();
            }
            catch(\Exception $e){

                return ['result' => 0,'msg' => $e->getMessage()];
            }
        }
        $url = url('/cms/settings/config_module',[$request->module_id]);
        return ['result' => 1,'msg' => 'Configurazione copiata con successo!','url' => $url];

    }

    public function store_config_module(Request $request)
    {
        try{
            $config_module = new ModuleConfig();
            $config_module->type = $request->type;
            $config_module->module_id = $request->module_id;
            $config_module->nome = $request->nome;
            $config_module->desc = $request->desc;
            if($request->type == 'boolean' && ($request->value != '1' && $request->value != '0'))
            {
                return ['result' => 0,'msg' => 'Se il campo è booleano il valore deve essere 0 o 1!'];
            }
            $config_module->value = $request->value;


            $config_module->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/settings/config_module',[$request->module_id]);
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    public function edit_config_module(Request $request, $id)
    {
        $config = ModuleConfig::find($id);
        $params = [
            'config' => $config,
            'form_name' => 'form_edit_config_module'
        ];

        return view('cms.settings.edit_config_module',$params);
    }

    public function update_config_module(Request $request, $id)
    {
        $config = ModuleConfig::find($id);

        try{
            $config->nome = $request->nome;
            $config->type = $request->type;
            $config->desc = $request->desc;
            if($request->type == 'boolean' && ($request->value != '1' && $request->value != '0'))
            {
                return ['result' => 0,'msg' => 'Se il campo è booleano il valore deve essere 0 o 1!'];
            }
            $config->value = $request->value;
            $config->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        $url = url('/cms/settings/config_module',[$request->module_id]);
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!','url' => $url];
    }

    public function switch_boolean_config(Request $request)
    {
        $id_config_module = $request->id;
        $stato = $request->stato;

        try{
            $modulo_config = ModuleConfig::find($id_config_module);
            $modulo_config->value = $stato;
            $modulo_config->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function switch_stato_module(Request $request)
    {
        $id_modulo = $request->id;
        $stato = $request->stato;

        try{
            $modulo = Module::find($id_modulo);
            $modulo->stato = $stato;
            $modulo->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function create_admin_user_pannello()
    {
        $config = \Config::get('website_config');
        $username = $config['username'];
        $email = 'support@inyourlife.info';
        $password = substr(encrypt($username),0,8);
        $nome = 'Inyourlife';
        $role_id = 1;

        $user_gia_presente = UserCms::where('email',$email)->first();
        if($user_gia_presente)
        {
            return back()->with('error','Utente già presente');
        }

        $user = UserCms::create([
            'role_id'  => $role_id,
            'name'     => $nome,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        //inserisco la password in chiaro
        $clear_pwd = new CmsClearpassword();
        $clear_pwd->user_id = $user->id;
        $clear_pwd->password = $password;
        $clear_pwd->save();

        return back()->with('success','Utente creato con successo');
    }

    public function create_user_pannello()
    {
        $config = \Config::get('website_config');
        $username = $config['username'];
        $password = substr(encrypt($username),0,8);
        $nome = $config['azienda'];
        $role_id = 2;

        $user_gia_presente = UserCms::where('email',$username)->first();
        if($user_gia_presente)
        {
            return back()->with('error','Utente già presente');
        }

        $user = UserCms::create([
            'role_id'  => $role_id,
            'name'     => $nome,
            'email'    => $username,
            'password' => Hash::make($password),
        ]);

        //inserisco la password in chiaro
        $clear_pwd = new CmsClearpassword();
        $clear_pwd->user_id = $user->id;
        $clear_pwd->password = $password;
        $clear_pwd->save();

        return back()->with('success','Utente creato con successo');

    }
}
