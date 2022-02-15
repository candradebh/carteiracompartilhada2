<?php

use App\Http\Controllers\ImportarCorretagensController;
use App\Http\Controllers\LockAuthController;
use App\Http\Controllers\OrdensController;
use App\Http\Controllers\Settings\UserController;
use App\Models\Carteira;
use App\Models\Corretoras;
use App\Models\Ordens;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render([
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ], 'Dashboard');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function (){
    Route::get('/', function () {
        $user_id = auth()->user()->id;
        $ano = date('Y');
        return Inertia::render('Dashboard',[
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'carteiras' => Carteira::where('user_id', $user_id )->get(),
            'comprasPorMesAnoAtual'=> Ordens::where('tipoordem','C')->whereBetween('data', [date('Ymd',strtotime($ano."0101")), date('Ymd',strtotime($ano."1231"))])
                                    ->whereHas('carteira', function ($query) use ($user_id){
                                        $query->where('carteiras.user_id',$user_id);
                                    })
                                    ->orderBy('data')
                                    ->get()
                                    ->groupBy(function ($val) {
                                        return Carbon::parse($val->data)->format('m');
                                    })->map(function ($row) {
                                        return $row->sum('total');
                                    }),
            'vendasPorMesAnoAtual'=> Ordens::where('tipoordem','V')->whereBetween('data', [date('Ymd',strtotime($ano."0101")), date('Ymd',strtotime($ano."1231"))])
                                    ->whereHas('carteira', function ($query) use ($user_id){
                                        $query->where('carteiras.user_id',$user_id);
                                    })
                                    ->orderBy('data')
                                    ->get()
                                    ->groupBy(function ($val) {
                                        return Carbon::parse($val->data)->format('m');
                                    })->map(function ($row) {
                                        return $row->sum('total');
                                    })
        ]);
    })->name('dashboard');

    //rotas de carteiras
    Route::prefix('carteiras')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Carteiras/Index',[
                                'carteiras' => Carteira::where('user_id', auth()->user()->id )->get()

                            ]);
        })->name('carteiras.index');

        Route::get('importar', function () {
            return Inertia::render('Carteiras/Importar',[
                'corretoras' => Corretoras::where('realizaimportacao',true)->get(),
                'carteiras'=> Carteira::where('user_id', auth()->user()->id)->get()
            ]);
        })->name('carteiras.importar');

        Route::post('importar', [ImportarCorretagensController::class, 'postUploadForm'])->name('carteiras.enviar');

        Route::resource('ordens', OrdensController::class);

     });

    /*They are the required pages for the system, don't delete it*/
    Route::prefix('settings')->group(function () {
       Route::get('/', function () {return Inertia::render('Settings/Index',[
           'users_count' => count(User::all('id')),
           'roles_count' => count(Role::all()),
           'permissions_count' => count(Permission::all())
       ]);})->name('settings');
       Route::resource('settings-user', UserController::class);
       Route::get('role', function () {return Inertia::render('Settings/Role');})->name('settings-role');
       Route::get('permission', function () {return Inertia::render('Settings/Permission');})->name('settings-permission');
       Route::get('system', function () {return Inertia::render('Settings/System');})->name('settings-system');
    });

    /*This pages for example, you can delete when you design the your system*/
    //Example Pages
    Route::get('login-app',function (){return Inertia::render('Samples/Examples/Login');})->name('login-app');
        Route::get('login-app-1',function (){return Inertia::render('Samples/Examples/Auth/Login1');})->name('login-app-1');
        Route::get('login-app-2',function (){return Inertia::render('Samples/Examples/Auth/Login2');})->name('login-app-2');
        Route::get('login-app-3',function (){return Inertia::render('Samples/Examples/Auth/Login3');})->name('login-app-3');
    Route::get('register-app',function (){return Inertia::render('Samples/Examples/Register');})->name('register-app');
        Route::get('register-app-1',function (){return Inertia::render('Samples/Examples/Auth/Register1');})->name('register-app-1');
        Route::get('register-app-2',function (){return Inertia::render('Samples/Examples/Auth/Register2');})->name('register-app-2');
        Route::get('register-app-3',function (){return Inertia::render('Samples/Examples/Auth/Register3');})->name('register-app-3');
    Route::get('forgot-password-app',function (){return Inertia::render('Samples/Examples/ForgotPassword');})->name('forgot-password-app');
        Route::get('forgot-password-app-1',function (){return Inertia::render('Samples/Examples/Auth/ForgotPassword1');})->name('forgot-password-app-1');
        Route::get('forgot-password-app-2',function (){return Inertia::render('Samples/Examples/Auth/ForgotPassword2');})->name('forgot-password-app-2');
        Route::get('forgot-password-app-3',function (){return Inertia::render('Samples/Examples/Auth/ForgotPassword3');})->name('forgot-password-app-3');
    Route::get('lock-app',function (){return Inertia::render('Samples/Examples/Lock');})->name('lock-app');
        Route::get('lock-app-1',function (){return Inertia::render('Samples/Examples/Auth/Lock1');})->name('lock-app-1');
        Route::get('lock-app-2',function (){return Inertia::render('Samples/Examples/Auth/Lock2');})->name('lock-app-2');
        Route::get('lock-app-3',function (){return Inertia::render('Samples/Examples/Auth/Lock3');})->name('lock-app-3');
    Route::get('profile',function (){return Inertia::render('Samples/Examples/Profile');})->name('profile');
    Route::get('pricing',function (){return Inertia::render('Samples/Examples/Pricing');})->name('pricing');
    Route::get('project-management-app',function (){return Inertia::render('Samples/Examples/ProjectApp');})->name('project-management-app');
    Route::get('todo-app',function (){return Inertia::render('Samples/Examples/TodoApp');})->name('todo-app');
    Route::get('email-app',function (){return Inertia::render('Samples/Examples/EmailApp');})->name('email-app');
    Route::get('chat-app',function (){return Inertia::render('Samples/Examples/ChatApp');})->name('chat-app');
    //Component Pages
    Route::get('alert',function (){return Inertia::render('Samples/Components/Alert');})->name('alert');
    Route::get('avatar',function (){return Inertia::render('Samples/Components/Avatar');})->name('avatar');
    Route::get('badge',function (){return Inertia::render('Samples/Components/Badge');})->name('badge');
    Route::get('breadcrumb',function (){return Inertia::render('Samples/Components/Breadcrumb');})->name('breadcrumb');
    Route::get('button',function (){return Inertia::render('Samples/Components/Button');})->name('button');
    Route::get('chart',function (){return Inertia::render('Samples/Components/Chart');})->name('chart');
    Route::get('collapsible',function (){return Inertia::render('Samples/Components/Collapsible');})->name('collapsible');
    Route::get('dropdown',function (){return Inertia::render('Samples/Components/Dropdown');})->name('dropdown');
    Route::get('list',function (){return Inertia::render('Samples/Components/List');})->name('list');
    Route::get('modal',function (){return Inertia::render('Samples/Components/Modal');})->name('modal');
    Route::get('pagination',function (){return Inertia::render('Samples/Components/Paginate');})->name('pagination');
    Route::get('popover',function (){return Inertia::render('Samples/Components/Popover');})->name('popover');
    Route::get('progress',function (){return Inertia::render('Samples/Components/Progress');})->name('progress');
    Route::get('tab',function (){return Inertia::render('Samples/Components/Tab');})->name('tab');
    Route::get('table',function (){return Inertia::render('Samples/Components/Table',[
        'users' => \App\Models\User::all()
    ]);})->name('table');

    Route::get('usuarios',function (){return Inertia::render('Samples/Components/Table',[
        'users' => \App\Models\User::all()
    ]);})->name('table');


    /*TODO: Toastr Feature
    Route::get('toastr',function (){return Inertia::render('Samples/Components/Toastr');})->name('toastr');*/
    Route::get('tooltip',function (){return Inertia::render('Samples/Components/Tooltip');})->name('tooltip');
    // Layout Pages
    Route::get('layout-structure',function (){return Inertia::render('Samples/Layouts/LayoutStructure');})->name('layout-structure');
    Route::get('layout-grid',function (){return Inertia::render('Samples/Layouts/Grid');})->name('layout-grid');
    Route::get('layout-content-box',function (){return Inertia::render('Samples/Layouts/ContentBox');})->name('layout-content-box');
    Route::get('layout-statistic-widget',function (){return Inertia::render('Samples/Layouts/StatisticWidget');})->name('layout-statistic-widget');
    // Form Pages
    Route::get('form-structure',function (){return Inertia::render('Samples/FormElements/FormStructure');})->name('form-structure');
    Route::get('form-input-group',function (){return Inertia::render('Samples/FormElements/InputGroup');})->name('form-input-group');
    Route::get('form-simple-field',function (){return Inertia::render('Samples/FormElements/SimpleField');})->name('form-simple-field');
    Route::get('form-repeatable-field',function (){return Inertia::render('Samples/FormElements/RepeatableField');})->name('form-repeatable-field');
    Route::get('form-inline-repeatable-field',function (){return Inertia::render('Samples/FormElements/InlineRepeatableField');})->name('form-inline-repeatable-field');
    Route::get('form-date-field',function (){return Inertia::render('Samples/FormElements/DateField');})->name('form-date-field');
    Route::get('form-select-input',function (){return Inertia::render('Samples/FormElements/SelectInput',[
        'users' => \App\Models\User::all()
    ]);})->name('form-select-input');
    Route::get('form-multi-select-input',function (){return Inertia::render('Samples/FormElements/MultiSelectInput',[
        'users' => \App\Models\User::all()
    ]);})->name('form-multi-select-input');
    Route::get('form-tag-input',function (){return Inertia::render('Samples/FormElements/TagInput');})->name('form-tag-input');
    Route::get('form-validation',function (){return Inertia::render('Samples/FormElements/Validation');})->name('form-validation');
});



