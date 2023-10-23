<?php

use App\Models\Article;
use App\Models\Consult;
use App\Models\Person;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/signup', function (Request $request) {
    $user = Person::where('email', '=', $request->input('email'))->first();
    if ($user) {
        return Response::json(['error' => 'email already exist'], 201);
    } else {
        $user = new Person();
        $user->firstName = $request->input('firstName');
        $user->lastName = $request->input('lastName');
        $user->age = $request->input('age');
        $user->type = $request->input('type');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->gender = $request->input('gender');
        if($request->has('special'))
            $user->special = $request->input('special');

        $user->image = '';
        if ($file = $request->file('image')) {
            $imageName = $file->getClientOriginalName() . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imageName);
            $user->image = $imageName;
        }

        if ($user->save()) {
            return Response::json(
                $user,
                200
            );
        } else {
            return Response::json(
                ['error' => 'can not save user'],
                202
            );
        }
    }
});



Route::post('/login', function (Request $request) {

    $email =  $request->input('email');
    $password = $request->input('password');
    $user =  Person::where('email', '=', $email)->where('password', '=', $password)->first();
    if ($user) {
        unset($user->password);
        return Response::json(
            $user,
            200
        );
    } else {
        return Response::json(['error' => 'User not found'], 404);
    }
});


Route::post('/profile', function (Request $request) {
    $id =  $request->input('id');

    $user =  Person::where('id', '=', $id)->first();
    if ($user) {
        unset($user->password);
        return Response::json(
            $user,
            200
        );
    } else {
        return Response::json(['error' => 'User not found'], 404);
    }
});

Route::post('/editProfile', function (Request $request) {
    $id =  $request->input('id');
    $user =  Person::where('id', '=', $id)->first();
    if ($user) {
        if ($request->has('firstName')) $user->firstName = $request->input('firstName');
        if ($request->has('lastName')) $user->lastName = $request->input('lastName');
        if ($request->has('age')) $user->age = $request->input('age');
        if ($request->has('email')) $user->email = $request->input('email');
        if ($request->has('password')) $user->password = $request->input('password');
        if ($request->has('gender')) $user->gender = $request->input('gender');


        if ($file = $request->file('image')) {
            $imageName = $file->getClientOriginalName() . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $imageName);
            $user->image = $imageName;
        }

        if ($user->update()) {
            unset($user->password);
            return Response::json(
                $user,
                200
            );
        } else {
            return Response::json(['error' => 'Error updating profile'], 201);
        }
    } else {
        return Response::json(['error' => 'User not found'], 404);
    }
});


Route::post('/addConsult', function (Request $request) {
    $userId = $request->input('userId');
    $message =  $request->input('message');
    $doctorId = '';
    if ($request->has('doctorId')) $doctorId = $request->input('doctorId');

    $consult = new Consult();
    $consult->userId = $userId;
    $consult->message = $message;
    $consult->doctorId = $doctorId;

    if ($consult->save()) {

        return Response::json(
            $consult,
            200
        );
    } else {
        return Response::json(['error' => 'Consult not stored'], 404);
    }
});


Route::post('/home', function (Request $request) {
    $doctors = Person::where('type', '=', '1')->get();
    $articles = Article::all();
    $consults = Consult::all();

    $res = [
        $doctors,
        $articles,
        $consults
    ];
    return Response::json(
        $res,
        200
    );
});

Route::post('/doctors', function (Request $request) {
    $doctors = Person::where('type', '=', '1')->get();

    return Response::json(
        $doctors,
        200
    );
});


Route::post('/search', function (Request $request) {
    $search = $request->input('search');

    $doctors = Person::where('type', '=', '1')->where(function($query)use($search){
        $query->where('firstName', 'LIKE', '%' . $search . '%')->orwhere('lastName', 'LIKE', '%' . $search . '%')->orwhere('special', 'LIKE', '%' . $search . '%');
    })->get();

    return Response::json(
        $doctors,
        200
    );
});


Route::post('/notification', function (Request $request) {
    $userId = $request->input('userId');

    $consults = Consult::where('userId', '=', $userId)->get();
    $res = array();
    $i = 0;
    foreach ($consults as $consult) {
        $treatment = Treatment::where('consultId', '=', $consult->id)->first();
        if ($treatment){
            $res[$i] = $treatment;
            $i = $i + 1;
        }
            //array_push($treatment, $consults);
    }
    //$res.array_push($consults);
    $result = [
        $consults,
        $res
    ];
    return Response::json(
        $result,
        200
    );
});


Route::post('/article', function (Request $request) {
    $articleId = $request->input('articleId');

    $article = Article::where('id', '=', $articleId)->get();

    if ($article) {
        return Response::json(
            $article,
            200
        );
    } else {
        return Response::json(['error' => 'Article not found'], 404);
    }
});








// Doctor


Route::post('/answerConsult', function (Request $request) {
    $doctorId =  $request->input('doctorId');
    $consultId = $request->input('consultId');
    $answer = $request->input('answer');
    $diagnostic = $request->input('diagnostic');


    $consult = Consult::where('id', '=', $consultId)->first();
    if ($consult) {
        $consult->answer = $answer;
        $consult->diagnostic = $diagnostic;
        $consult->doctorId = $doctorId;
        if ($consult->update()) {
            return Response::json(
                $consult,
                200
            );
        } else {
            return Response::json(
                ['error' => 'Can not update consult'],
                201
            );
        }
    } else {
        return Response::json(['error' => 'Consult not found'], 404);
    }
});

Route::post('/consult', function (Request $request) {
    $consultId = $request->input('consultId');
    $consult = Consult::where('id', '=', $consultId)->first();
    $userId = $consult->userId;
    $user = Person::where('id', '=', $userId)->first();
    $consult->firstName = $user->firstName;
    $consult->lastName = $user->lastName;
    $consult->age = $user->age;
    $consult->image = $user->image;
    if ($consult) {
        return Response::json(
            $consult,
            200
        );
    } else {
        return Response::json(['error' => 'Consult not found'], 404);
    }
});



Route::post('/consults', function (Request $request) {
    $consults = Consult::where('answer', '=', null)->get();
    foreach ($consults as $consult) {
        $user = Person::where('id', '=', $consult->userId)->first();
        $consult->firstName = $user->firstName;
    }
    if ($consults) {
        return Response::json(
            $consults,
            200
        );
    } else {
        return Response::json(['error' => 'No Counsults found'], 404);
    }
});

Route::post('/doctorConsults', function (Request $request) {
    $doctorId = $request->input('doctorId');

    $consults = Consult::where('answer', '=', null)->where('doctorId', '=', $doctorId)->get();
    foreach ($consults as $consult) {
        $user = Person::where('id', '=', $consult->userId)->first();
        $consult->firstName = $user->firstName;
    }
    if ($consults) {
        return Response::json(
            $consults,
            200
        );
    } else {
        return Response::json(['error' => 'No Counsults found'], 404);
    }
});


Route::post('/addArticle', function (Request $request) {

    $article = new Article();

    $doctorId = $request->input('doctorId');
    $articleText = $request->input('article');

    $article->doctorId = $doctorId;
    $article->article = $articleText;
    $article->image = '';

    if ($file = $request->file('image')) {
        $imageName = $file->getClientOriginalName() . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images'), $imageName);
        $article->image = $imageName;
    }

    if ($article->save()) {
        return Response::json(
            $article,
            200
        );
    } else {
        return Response::json(['error' => 'Article not stored'], 201);
    }
});


Route::post('/addTreatment', function (Request $request) {
    $consultId = $request->input('consultId');
    $doctorId = $request->input('doctorId');

    $illness = $request->input('illness');
    $symptoms = $request->input('symptoms');
    $resons = $request->input('resons');
    $methods = $request->input('methods');


    $treatment = new Treatment();
    $treatment->doctorId = $doctorId;
    $treatment->consultId = $consultId;
    $treatment->illness = $illness;
    $treatment->symptoms = $symptoms;
    $treatment->resons = $resons;
    $treatment->methods = $methods;

    if ($treatment->save()) {
        return Response::json(
            $treatment,
            200
        );
    } else {
        return Response::json(['error' => 'Treatment not stored'], 201);
    }
});

Route::post('/treatments', function (Request $request) {
    $treatments = Consult::where('answer', '!=', null)->get();
    foreach ($treatments as $consult) {
        $user = Person::where('id', '=', $consult->userId)->first();
        $treatments->firstName = $user->firstName;
    }

    foreach ($treatments as $treat) {
        $treatment = Treatment::where('consultId', '=', $treat->id)->first();
        if ($treatment) unset($treat);
    }

    if ($treatments) {
        return Response::json(
            $treatments,
            200
        );
    } else {
        return Response::json(['error' => 'No Treatment found'], 404);
    }
});

Route::post('/doctorNotification', function (Request $request) {
    $doctorId = $request->input('doctorId');

    $consults = Consult::where('doctorId', '=', $doctorId)->where('answer', '=', null)->get();

    return Response::json(
        $consults,
        200
    );
});
