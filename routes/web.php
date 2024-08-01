<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CourseInfoController;
use App\Http\Controllers\DoubtsController;
use App\Http\Controllers\EducationalProgramInfoController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\GeneralNotificationsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PageFooterController;
use App\Http\Controllers\Profile\CategoriesController;
use App\Http\Controllers\Profile\MyProfileController;
use App\Http\Controllers\Profile\NotificationsController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SearcherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecoverPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ResourceInfoController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\Webhooks\ProcessPaymentRedsys;
use App\Http\Controllers\Webhooks\UpdateLoginSystemsController;
use App\Http\Controllers\CertificateAccessController;
use App\Http\Controllers\HeaderFooterPagesController;
use App\Http\Controllers\Profile\CompetencesLearningResultsController;
use App\Http\Controllers\Profile\MyCourses\EnrolledCoursesController;
use App\Http\Controllers\Profile\MyCourses\HistoricCoursesController;
use App\Http\Controllers\Profile\MyCourses\InscribedCoursesController;
use App\Http\Controllers\Profile\MyEducationalPrograms\EnrolledEducationalProgramsController;
use App\Http\Controllers\Profile\MyEducationalPrograms\HistoricEducationalProgramsController;
use App\Http\Controllers\Profile\MyEducationalPrograms\InscribedEducationalProgramsController;
use App\Http\Controllers\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('index');

Route::post('/home/save_lanes_preferences', [HomeController::class, 'saveLanesPreferences']);

Route::get('/doubts/{learning_object_type}/{uid}', [DoubtsController::class, 'index']);

Route::post('/doubts/send_doubt', [DoubtsController::class, 'sendDoubt']);

Route::get('/suggestions', [SuggestionsController::class, 'index']);
Route::post('/suggestions/send_suggestion', [SuggestionsController::class, 'sendSuggestion']);

Route::get('/recurso', [ResourceController::class, 'index']);

Route::get('/searcher', [SearcherController::class, 'index'])->name("searcher");

Route::post('/searcher/get_learning_objects', [SearcherController::class, 'getLearningObjects']);

Route::get('/page/{slug}', [HeaderFooterPagesController::class, 'index']);


Route::get('/course/{uid}', [CourseInfoController::class, 'index']);

Route::middleware(['combined.auth'])->group(function () {
    Route::post('/course/calificate', [CourseInfoController::class, 'calificate']);
    Route::post('/course/get_course_calification', [CourseInfoController::class, 'getCourseCalification']);

});

Route::get('/educational_program/{uid}', [EducationalProgramInfoController::class, 'index']);

Route::middleware(['combined.auth'])->group(function () {
    Route::post('/educational_program/calificate', [EducationalProgramInfoController::class, 'calificate']);
});
Route::get('/educational_program/get_educational_program/{uid}', [EducationalProgramInfoController::class, 'getEducationalProgramApi']);

Route::get('/resource/{uid}', [ResourceInfoController::class, 'index']);
Route::get('/resource/get_resource/{uid}', [ResourceInfoController::class, 'getResource']);

Route::post('/resource/access_resource', [ResourceInfoController::class, 'saveAccessResource']);



Route::middleware(['combined.auth'])->group(function () {
    Route::get('/cart/{learning_object_type}/{uid}', [CartController::class, 'index']);
    Route::post('/cart/make_payment', [CartController::class, 'makePayment']);
    Route::post('/cart/inscribe', [CartController::class, 'inscribe']);
});


Route::middleware(['combined.auth'])->group(function () {
    Route::post('/resource/calificate', [ResourceInfoController::class, 'calificate']);
});

Route::middleware(['combined.auth'])->group(function () {
    Route::get('/profile/categories', [CategoriesController::class, 'index'])->name('categories');
    Route::post('/profile/categories/save_categories', [CategoriesController::class, 'saveCategories'])->name('save-categories');

    Route::get('/profile/notifications', [NotificationsController::class, 'index'])->name('notifications');
    Route::post('/profile/notifications/save_notifications', [NotificationsController::class, 'saveNotifications'])->name('save-notifications');

});

Route::middleware(['combined.auth'])->group(function () {
    Route::get('/notifications/general/get_general_notification_user/{notification_general_uid}', [GeneralNotificationsController::class, 'getGeneralNotificationUser']);
    Route::get('/notifications/general/get_general_notification_automatic_user/{notification_automatic_uid}', [GeneralNotificationsController::class, 'getGeneralNotificationAutomaticUser']);
});

Route::middleware(['combined.auth'])->group(function () {
    Route::get('/profile/competences_learning_results', [CompetencesLearningResultsController::class, 'index'])->name("competences-learning-results");
    Route::post('/profile/competences_learning_results/save_learning_results', [CompetencesLearningResultsController::class, 'saveLearningResults']);
});

Route::middleware(['combined.auth'])->group(function () {
    Route::get('/profile/update_account', [MyProfileController::class, 'index'])->name('my-profile');
    Route::post('/profile/update_account/update', [MyProfileController::class, 'updateUser']);
});

Route::middleware(['combined.auth'])->group(function () {
    Route::post('/home/get_active_courses', [HomeController::class, 'getActiveCourses'])->name('get-active-courses');
    Route::post('/home/get_inscribed_courses', [HomeController::class, 'getInscribedCourses'])->name('get-inscribed-courses');
    Route::post('/home/get_teacher_courses', [HomeController::class, 'getTeacherCourses'])->name('get-teacher-courses');
});

Route::middleware(['combined.auth'])->group(function () {
    Route::get('/profile/my_courses/inscribed', [InscribedCoursesController::class, 'index'])->name('my-courses-inscribed');

    Route::post('/profile/my_courses/inscribed/get', [InscribedCoursesController::class, 'getInscribedCourses'])->name('get-inscribed-courses');
    Route::post('/profile/my_courses/inscribed/enroll_course', [InscribedCoursesController::class, 'enrollCourse'])->name('enroll-course-inscribed');

    Route::post('/profile/inscribed_courses/save_documents_course', [InscribedCoursesController::class, 'saveDocumentsCourse']);

    Route::post('/profile/inscribed_courses/download_document_course', [InscribedCoursesController::class, 'downloadDocumentCourse']);

    Route::get('/profile/my_courses/enrolled', [EnrolledCoursesController::class, 'index'])->name('my-courses-enrolled');
    Route::post('/profile/my_courses/enrolled/get', [EnrolledCoursesController::class, 'getEnrolledCourses'])->name('get-enrolled-courses');
    Route::post('/profile/my_courses/enrolled/access_course', [EnrolledCoursesController::class, 'accessCourse'])->name('enrolled-courses-access');

    Route::get('/profile/my_courses/historic', [HistoricCoursesController::class, 'index'])->name('my-courses-historic');
    Route::post('/profile/my_courses/historic/get', [HistoricCoursesController::class, 'getHistoricCourses'])->name('get-historic-courses');
    Route::post('/profile/my_courses/historic/access_course', [HistoricCoursesController::class, 'accessCourse'])->name('historic-courses-access');



    Route::get('/profile/my_educational_programs/inscribed', [InscribedEducationalProgramsController::class, 'index'])->name('my-educational-programs-inscribed');
    Route::post('/profile/my_educational_programs/inscribed/get', [InscribedEducationalProgramsController::class, 'getInscribedEducationalPrograms'])->name('get-inscribed-educational-programs');
    Route::post('/profile/my_educational_programs/save_documents_educational_program', [InscribedEducationalProgramsController::class, 'saveDocumentsEducationalProgram']);

    Route::post('/profile/my_educational_programs/inscribed/enroll_educational_program', [InscribedEducationalProgramsController::class, 'enrollEducationalProgram'])->name('enroll-educational-program-inscribed');
    Route::post('/profile/my_educational_programs/inscribed/download_document_educational_program', [InscribedEducationalProgramsController::class, 'downloadDocumentEducationalProgram']);

    Route::get('/profile/my_educational_programs/enrolled', [EnrolledEducationalProgramsController::class, 'index'])->name('my-educational-programs-enrolled');
    Route::post('/profile/my_educational_programs/enrolled/get', [EnrolledEducationalProgramsController::class, 'getEnrolledEducationalPrograms'])->name('my-educational-programs-enrolled-get');
    Route::post('/profile/my_educational_programs/enrolled/access_course', [EnrolledEducationalProgramsController::class, 'accessCourse'])->name('my-educational-programs-enrolled-access-course');

    Route::get('/profile/my_educational_programs/historic', [HistoricEducationalProgramsController::class, 'index'])->name('my-educational-programs-historic');
    Route::post('/profile/my_educational_programs/historic/get', [HistoricEducationalProgramsController::class, 'getHistoricEducationalPrograms'])->name('my-educational-programs-historic-get');
    Route::post('/profile/my_educational_programs/historic/access_course', [HistoricEducationalProgramsController::class, 'accessCourse'])->name('my-educational-programs-historic-access-course');

});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::get('/register', [RegisterController::class, 'index'])->name('login');


    Route::get('/forgot_password', [LoginController::class, 'index']);

    Route::post('/login/authenticate', [LoginController::class, 'authenticate']);

    Route::get('/auth/google', [LoginController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

    Route::get('/auth/twitter', [LoginController::class, 'redirectToTwitter']);
    Route::get('/auth/twitter/callback', [LoginController::class, 'handleTwitterCallback']);

    Route::get('/auth/linkedin',  [LoginController::class, 'redirectToLinkedin']);
    Route::get('/auth/linkedin/callback',  [LoginController::class, 'handleLinkedinCallback']);

    Route::get('/auth/facebook', [LoginController::class, 'redirectToFacebook']);
    Route::get('/auth/facebook/callback', [LoginController::class, 'handleFacebookCallback']);

});



Route::post('/register/submit', [RegisterController::class, 'submit'])->name('registerUser');

Route::get('/logout', [LoginController::class, 'logout']);
Route::get('/recover_password', [RecoverPasswordController::class, 'index'])->name('recover-password');
Route::post('/recover_password/send', [RecoverPasswordController::class, 'recoverPassword']);

Route::get('/reset_password/{token}', [ResetPasswordController::class, 'index'])->name('reset-password');
Route::post('/reset_password/send', [ResetPasswordController::class, 'resetPassword']);

Route::post('/api/update_login_system', [UpdateLoginSystemsController::class, 'index'])->middleware('verifyApiKey');
Route::post('/webhook/process_payment_redsys', [ProcessPaymentRedsys::class, 'index'])->name('webhook_process_payment_redsys');

Route::get('/error/{code}', [ErrorController::class, 'index'])->name('error');

Route::get('/certificate-access', [CertificateAccessController::class, 'index'])->name('certificate-access');
Route::get('/token_login/{token}', [LoginController::class, 'tokenLogin']);

// Ruta para mostrar el formulario de restablecimiento de contraseña
Route::get('password/reset/{token}', [ResetPasswordController::class, 'index'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
