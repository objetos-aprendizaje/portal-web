<?php

use App\Http\Controllers\AcceptancePoliciesController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CertificateAccessController;
use App\Http\Controllers\CourseInfoController;
use App\Http\Controllers\DoubtsController;
use App\Http\Controllers\EducationalProgramInfoController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\GeneralNotificationsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Profile\CategoriesController;
use App\Http\Controllers\Profile\MyProfileController;
use App\Http\Controllers\Profile\NotificationsController;
use App\Http\Controllers\SearcherController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecoverPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ResourceInfoController;
use App\Http\Controllers\SuggestionsController;
use App\Http\Controllers\Webhooks\ProcessPaymentRedsys;
use App\Http\Controllers\HeaderFooterPagesController;
use App\Http\Controllers\Profile\CompetencesLearningResultsController;
use App\Http\Controllers\Profile\MyCourses\EnrolledCoursesController;
use App\Http\Controllers\Profile\MyCourses\HistoricCoursesController;
use App\Http\Controllers\Profile\MyCourses\InscribedCoursesController;
use App\Http\Controllers\Profile\MyEducationalPrograms\EnrolledEducationalProgramsController;
use App\Http\Controllers\Profile\MyEducationalPrograms\HistoricEducationalProgramsController;
use App\Http\Controllers\Profile\MyEducationalPrograms\InscribedEducationalProgramsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\GetEmailController;
use App\Http\Controllers\Profile\Notifications\ProfileEmailNotificationsController;
use App\Http\Controllers\Profile\Notifications\ProfileGeneralNotificationsController;

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

Route::middleware('policies')->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::post('/home/save_lanes_preferences', [HomeController::class, 'saveLanesPreferences']);

    Route::get('/doubts/{learning_object_type}/{uid}', [DoubtsController::class, 'index']);
    Route::post('/doubts/send_doubt', [DoubtsController::class, 'sendDoubt']);

    Route::get('/suggestions', [SuggestionsController::class, 'index']);
    Route::post('/suggestions/send_suggestion', [SuggestionsController::class, 'sendSuggestion']);

    Route::get('/searcher', [SearcherController::class, 'index'])->name("searcher");
    Route::post('/searcher/get_learning_objects', [SearcherController::class, 'getLearningObjects']);
    Route::get('/searcher/get_learning_results/{query}', [SearcherController::class, 'searchLearningResults']);

    Route::get('/course/{uid}', [CourseInfoController::class, 'index']);

    Route::get('/educational_program/{uid}', [EducationalProgramInfoController::class, 'index']);
    Route::get('/educational_program/get_educational_program/{uid}', [EducationalProgramInfoController::class, 'getEducationalProgramApi']);

    Route::get('/resource/{uid}', [ResourceInfoController::class, 'index']);
    Route::get('/resource/get_resource/{uid}', [ResourceInfoController::class, 'getResource']);

    Route::middleware(['combined.auth'])->group(function () {
        Route::post('/home/get_active_courses', [HomeController::class, 'getActiveCourses'])->name('get-active-courses');
        Route::post('/home/get_inscribed_courses', [HomeController::class, 'getInscribedCourses'])->name('get-inscribed-courses');
        Route::post('/home/get_my_educational_resources', [HomeController::class, 'getMyEducationalResources'])->name('get-my-educational-resources');
        Route::post('/home/get_recommended_itinerary', [HomeController::class, 'getRecommendedItinerary'])->name('get-recommended-itinerary');

        Route::post('/home/get_teacher_courses', [HomeController::class, 'getTeacherCourses'])->name('get-teacher-courses');
        Route::post('/home/get_recommended_courses', [HomeController::class, 'getRecommendedCourses'])->name('get-recommended-courses');
        Route::post('/home/get_recommended_educational_resources', [HomeController::class, 'getRecommendedEducationalResources'])->name('get-recommended-educational-resources');

        Route::post('/course/calificate', [CourseInfoController::class, 'calificate']);
        Route::post('/course/get_course_calification', [CourseInfoController::class, 'getCourseCalification']);

        Route::get('/cart/{learning_object_type}/{uid}', [CartController::class, 'index']);
        Route::post('/cart/make_payment', [CartController::class, 'makePayment']);
        Route::post('/cart/inscribe', [CartController::class, 'inscribe']);

        Route::post('/educational_program/calificate', [EducationalProgramInfoController::class, 'calificate']);

        Route::post('/resource/calificate', [ResourceInfoController::class, 'calificate']);

        Route::get('/profile/categories', [CategoriesController::class, 'index'])->name('categories');
        Route::post('/profile/categories/save_categories', [CategoriesController::class, 'saveCategories'])->name('save-categories');

        Route::get('/profile/notifications/general', [ProfileGeneralNotificationsController::class, 'index'])->name('profile-general-notifications');
        Route::post('/profile/notifications/general/save', [ProfileGeneralNotificationsController::class, 'saveNotifications']);

        Route::get('/profile/notifications/email', [ProfileEmailNotificationsController::class, 'index'])->name('profile-email-notifications');
        Route::post('/profile/notifications/email/save', [ProfileEmailNotificationsController::class, 'saveNotifications']);

        Route::get('/profile/competences_learning_results', [CompetencesLearningResultsController::class, 'index'])->name("competences-learning-results");
        Route::post('/profile/competences_learning_results/save_learning_results', [CompetencesLearningResultsController::class, 'saveLearningResults']);

        Route::get('/profile/update_account', [MyProfileController::class, 'index'])->name('my-profile');
        Route::post('/profile/update_account/update', [MyProfileController::class, 'updateUser']);
        Route::delete('/profile/update_account/delete_image', [MyProfileController::class, 'deleteImage']);

        Route::get('/profile/my_courses/inscribed', [InscribedCoursesController::class, 'index'])->name('my-courses-inscribed');

        Route::post('/profile/my_courses/inscribed/get', [InscribedCoursesController::class, 'getInscribedCourses']);
        Route::post('/profile/my_courses/inscribed/enroll_course', [InscribedCoursesController::class, 'enrollCourse'])->name('enroll-course-inscribed');
        Route::post('/profile/my_courses/inscribed/cancel_inscription', [InscribedCoursesController::class, 'cancelInscription'])->name('enroll-course-cancel-inscription');

        Route::post('/profile/inscribed_courses/save_documents_course', [InscribedCoursesController::class, 'saveDocumentsCourse']);

        Route::post('/profile/inscribed_courses/download_document_course', [InscribedCoursesController::class, 'downloadDocumentCourse']);

        Route::get('/profile/my_courses/enrolled', [EnrolledCoursesController::class, 'index'])->name('my-courses-enrolled');
        Route::post('/profile/my_courses/enrolled/get', [EnrolledCoursesController::class, 'getEnrolledCourses'])->name('get-enrolled-courses');
        Route::post('/profile/my_courses/enrolled/access_course', [EnrolledCoursesController::class, 'accessCourse'])->name('enrolled-courses-access');
        Route::post('/profile/my_courses/enrolled/pay_term', [EnrolledCoursesController::class, 'payTerm'])->name('enrolled-courses-pay-term');


        Route::get('/profile/my_courses/historic', [HistoricCoursesController::class, 'index'])->name('my-courses-historic');
        Route::post('/profile/my_courses/historic/get', [HistoricCoursesController::class, 'getHistoricCourses'])->name('get-historic-courses');
        Route::post('/profile/my_courses/historic/access_course', [HistoricCoursesController::class, 'accessCourse'])->name('historic-courses-access');

        Route::get('/profile/my_educational_programs/inscribed', [InscribedEducationalProgramsController::class, 'index'])->name('my-educational-programs-inscribed');
        Route::post('/profile/my_educational_programs/inscribed/get', [InscribedEducationalProgramsController::class, 'getInscribedEducationalPrograms'])->name('get-inscribed-educational-programs');
        Route::post('/profile/my_educational_programs/save_documents_educational_program', [InscribedEducationalProgramsController::class, 'saveDocumentsEducationalProgram']);

        Route::post('/profile/my_educational_programs/inscribed/enroll_educational_program', [InscribedEducationalProgramsController::class, 'enrollEducationalProgram'])->name('enroll-educational-program-inscribed');
        Route::post('/profile/my_educational_programs/inscribed/download_document_educational_program', [InscribedEducationalProgramsController::class, 'downloadDocumentEducationalProgram']);
        Route::post('/profile/my_educational_programs/inscribed/cancel_inscription', [InscribedEducationalProgramsController::class, 'cancelInscription']);


        Route::get('/profile/my_educational_programs/enrolled', [EnrolledEducationalProgramsController::class, 'index'])->name('my-educational-programs-enrolled');
        Route::post('/profile/my_educational_programs/enrolled/get', [EnrolledEducationalProgramsController::class, 'getEnrolledEducationalPrograms'])->name('my-educational-programs-enrolled-get');
        Route::post('/profile/my_educational_programs/enrolled/access_course', [EnrolledEducationalProgramsController::class, 'accessCourse'])->name('my-educational-programs-enrolled-access-course');
        Route::post('/profile/my_educational_programs/enrolled/pay_term', [EnrolledEducationalProgramsController::class, 'payTerm'])->name('my-educational-programs-enrolled-pay-term');

        Route::get('/profile/my_educational_programs/historic', [HistoricEducationalProgramsController::class, 'index'])->name('my-educational-programs-historic');
        Route::post('/profile/my_educational_programs/historic/get', [HistoricEducationalProgramsController::class, 'getHistoricEducationalPrograms'])->name('my-educational-programs-historic-get');
        Route::post('/profile/my_educational_programs/historic/access_course', [HistoricEducationalProgramsController::class, 'accessCourse'])->name('my-educational-programs-historic-access-course');

        Route::get('/notifications/general/get_general_notification_user/{notification_general_uid}', [GeneralNotificationsController::class, 'getGeneralNotificationUser']);
        Route::get('/notifications/general/get_general_notification_automatic_user/{notification_automatic_uid}', [GeneralNotificationsController::class, 'getGeneralNotificationAutomaticUser']);
    });
});

Route::get('/page/{slug}', [HeaderFooterPagesController::class, 'index']);

Route::get('/accept_policies', [AcceptancePoliciesController::class, 'index'])->name('policiesAccept');
Route::post('/accept_policies/submit', [AcceptancePoliciesController::class, 'acceptPolicies']);

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/register/resend_email_confirmation', [RegisterController::class, 'resendEmailConfirmation']);
Route::post('/register/submit', [RegisterController::class, 'submit'])->name('registerUser');
Route::get('/email/verify/{token}', [RegisterController::class, 'verifyEmail'])->name('verification.verify');

Route::get('/recover_password', [RecoverPasswordController::class, 'index'])->name('recover-password');
Route::post('/recover_password/send', [RecoverPasswordController::class, 'recoverPassword']);

// Ruta para mostrar el formulario de restablecimiento de contraseña
Route::get('/reset_password/{token}', [ResetPasswordController::class, 'index'])->name('reset-password');
Route::post('/reset_password/send', [ResetPasswordController::class, 'resetPassword']);
Route::get('password/reset/{token}', [ResetPasswordController::class, 'index'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
Route::get('/error/{code}', [ErrorController::class, 'index'])->name('error');

Route::get('/get-email', [GetEmailController::class, 'index'])->name('get-email');
Route::post('/get-email/add', [GetEmailController::class, 'addUser'])->name('add-user');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::get('/login/certificate', [CertificateAccessController::class, 'loginCertificate'])->name('login-certificate');
    Route::get('/register', [RegisterController::class, 'index'])->name('register');


    Route::get('/forgot_password', [LoginController::class, 'index']);

    Route::post('/login/authenticate', [LoginController::class, 'authenticate']);

    Route::get('auth/{login_method}', [LoginController::class, 'redirectToSocialLogin']);
    Route::get('/auth/callback/{login_method}', [LoginController::class, 'handleSocialCallback']);
});

// Webhooks
Route::post('/webhook/process_payment_redsys', [ProcessPaymentRedsys::class, 'index'])->name('webhook_process_payment_redsys');
