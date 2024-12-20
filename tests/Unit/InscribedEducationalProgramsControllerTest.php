<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\UsersModel;
use App\Models\ApiKeysModel;
use App\Models\CoursesModel;
use App\Models\HeaderPagesModel;
use Illuminate\Http\UploadedFile;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use App\Models\EducationalProgramsModel;
use App\Models\EducationalProgramStatusesModel;
use App\Models\EducationalProgramsPaymentsModel;
use App\Models\EducationalProgramsStudentsModel;
use App\Models\EducationalProgramsDocumentsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\EducationalProgramsPaymentTermsModel;
use App\Models\EducationalProgramsStudentsDocumentsModel;



class InscribedEducationalProgramsControllerTest extends TestCase
{
    /**
     * @group configdesistema
     */
    use RefreshDatabase;

    /**
     * @testdox Inicialización de inicio de sesión
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $general_options = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();
        app()->instance('general_options', $general_options);
        View::share('general_options', $general_options);

        $footer_pages = [
            ['slug' => 'page1', 'name' => 'Page 1'],
            ['slug' => 'page2', 'name' => 'Page 2'],
        ];
        View::share('footer_pages', $footer_pages);

        // Define la variable $fonts con todas las claves necesarias
        $fonts = [
            'truetype_regular_file_path' => asset('fonts/robot/roboto.ttf'), // Cambia esto a la ruta real
            'woff_regular_file_path' => asset('fonts/robot/roboto.woff'), // Cambia esto a la ruta real
            'woff2_regular_file_path' => asset('fonts/robot/roboto.woff2'), // Cambia esto a la ruta real
            'embedded_opentype_regular_file_path' => asset('fonts/robot/roboto.eot'), // Cambia esto a la ruta real
            'opentype_regular_input_file' => asset('fonts/robot/roboto.otf'), // Cambia esto a la ruta real
            'svg_regular_file_path' => asset('fonts/robot/roboto.svg'), // Asegúrate de incluir esta clave
            'truetype_medium_file_path' => asset('fonts/robot/roboto-medium.ttf'), // Replace with the actual path
            'woff_medium_file_path' => asset('fonts/robot/roboto-medium.woff'),
            'woff2_medium_file_path' => asset('fonts/robot/roboto-medium.woff2'),
            'embedded_opentype_medium_file_path' => asset('fonts/robot/roboto.eot'),
            'opentype_medium_file_path' => asset('fonts/robot/roboto.otf'),
            'svg_medium_file_path' => asset('fonts/robot/roboto.svg'),
            'truetype_bold_file_path' =>  asset('fonts/robot/roboto-bold.ttf'),
            'woff_bold_file_path' => asset('fonts/robot/roboto-bold.woff'),
            'woff2_bold_file_path' => asset('fonts/robot/roboto-bold.woff2'),
            'embedded_opentype_bold_file_path' => asset('fonts/robot/roboto.eot'),
            'opentype_bold_file_path' => asset('fonts/robot/roboto.otf'),
            'svg_bold_file_path' => asset('fonts/robot/roboto.svg'),
        ];
        // Comparte la variable $fonts para esta prueba
        View::share('fonts', $fonts);

        $headerPages = HeaderPagesModel::whereNull('header_page_uid')->with('headerPagesChildren')->orderBy('order', 'asc')->get();

        // Comparte la variable $header_pages para esta prueba
        View::share('header_pages', $headerPages);
    }
    /**
     * @test Index Inscritos Programa formativo
     */

    public function testIndexViewInscribedPrograms()
    {
        // Simular la autenticación del usuario si es necesario
        $this->actingAs(UsersModel::factory()->create());

        $general_options  = [
            'redsys_url' => 'http://midominio.com',
            'color_1' => '#333333',
            'color_2' => '#222',
            'color_3' => '#111',
            'color_4' => '#000',
            'scripts' => '<script src="jquery.js"></script>',
            'poa_logo_1' => 'image1.png',
            'poa_logo_2' => 'image2.png',
            'poa_logo_3' => 'image3.png',
            'registration_active' => false,
        ];

        app()->instance('general_options', $general_options);

        View::share('general_options', $general_options);

        // Realizar una solicitud GET a la ruta definida
        $response = $this->get(route('my-educational-programs-inscribed'));

        // Verificar que la respuesta sea exitosa (código 200)
        $response->assertStatus(200);

        // Verificar que se retorne la vista correcta
        $response->assertViewIs('profile.my_educational_programs.inscribed_educational_programs.index');

        // Verificar que los datos pasados a la vista sean correctos
        $response->assertViewHas('resources', [
            'resources/js/profile/my_educational_programs/inscribed_educational_programs.js'
        ]);
        $response->assertViewHas('page_title', 'Mis programas formativos inscritos');
        $response->assertViewHas('currentPage', 'inscribedEducationalPrograms');
    }

    /** @test */
    public function testReturnsInscribedEducationalPrograms()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'INSCRIPTION')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create(
            [
                'educational_program_status_uid' => $status->uid,
            ]
        )->first();

        EducationalProgramsPaymentTermsModel::factory()->create([
            'educational_program_uid' => $educationalProgram->uid
        ]);


        $document = EducationalProgramsDocumentsModel::factory()->create(
            [
                'educational_program_uid' => $educationalProgram->uid
            ]
        );

        EducationalProgramsStudentsDocumentsModel::factory()->create(
            [
                'educational_program_document_uid' => $document->uid,
                'user_uid' => $user->uid,
                'document_path' => 'file/file.pdf'
            ]
        );

        CoursesModel::factory()->withCourseStatus()->withCourseType()->create(
            [
                'educational_program_uid' => $educationalProgram->uid
            ]
        );

        $user->educationalPrograms()->attach(
            $educationalProgram->uid,
            [
                'uid' => generate_uuid(),
                'status' => 'INSCRIBED',
                'acceptance_status' => 'ACCEPTED',
            ]
        );

        // Preparar los datos de la solicitud
        $data = [
            'items_per_page' => 10,
            'search' => null,
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(route('get-inscribed-educational-programs'), $data);

        // Verificar que la respuesta sea exitosa (código 200)
        $response->assertStatus(200);

        // Verificar que se retorne un JSON con los programas educativos correctos
        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'uid',
                    'name',
                    'description',
                    // Agrega aquí otros campos que esperas en la respuesta
                ]
            ],
            'last_page',
            'total',
        ]);

        // Verificar que el programa educativo esté en los resultados
        $this->assertEquals($educationalProgram->id, $response->json('data.0.id'));
    }

    /** @test */
    public function testFiltersInscribedProgramsBySearchTerm()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        // Crear programas educativos con diferentes nombres y descripciones
        $programs1 = EducationalProgramsModel::factory()->withEducationalProgramType()->create(['uid' => generate_uuid(), 'name' => 'Curso de Matemáticas'])->first();

        $programs2 = EducationalProgramsModel::factory()->withEducationalProgramType()->create(['uid' => generate_uuid(), 'name' => 'Curso de Física'])->latest()->first();


        // Comprobar si el usuario ya está inscrito en el programa
        if (!$user->educationalPrograms()->where('educational_program_uid', $programs1->uid)->exists()) {
            $user->educationalPrograms()->attach($programs1->uid, ['uid' => generate_uuid(), 'status' => 'INSCRIBED', 'acceptance_status' => 'ACCEPTED', 'educational_program_uid' => $programs1->uid]);
        }

        if (!$user->educationalPrograms()->where('educational_program_uid', $programs2->uid)->exists()) {
            $user->educationalPrograms()->attach($programs2->uid, ['uid' => generate_uuid(), 'status' => 'INSCRIBED', 'educational_program_uid' => $programs2->uid]);
        }


        // Preparar los datos de la solicitud con un término de búsqueda
        $data = [
            'items_per_page' => 10,
            'search' => 'Matemáticas', // Término a buscar
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(route('get-inscribed-educational-programs'), $data);

        // Verificar que la respuesta sea exitosa (código 200)
        $response->assertStatus(200);
    }

    /** @test Error 406 - Esta matrículado en el curso solicitado*/

    public function testAllows406UserEnrollProgram()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'ENROLLING')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid
        ])->first();

        $user->educationalPrograms()->attach($educationalProgram, ['uid' => generate_uuid(), 'status' => 'ENROLLED', 'acceptance_status' => 'ACCEPTED', 'educational_program_uid' => $educationalProgram->uid]);

        // Preparar los datos de la solicitud
        $data = [
            'educationalProgramUid' => $educationalProgram->uid,
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(route('enroll-educational-program-inscribed'), $data);

        // Verificar que la respuesta sea exitosa (código 200)
        $response->assertStatus(406);
        $response->assertJson(['message' => 'Ya estás matriculado en este curso']);
    }


    /** @test Error 406 Por que el status del programa es diferente a ENROLLING*/
    public function testUser406EnrollingIfCourseIsNotEnrolling()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'INSCRIPTION')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid
        ])->first();

        // Preparar los datos de la solicitud
        $data = [
            'educationalProgramUid' => $educationalProgram->uid,
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(route('enroll-educational-program-inscribed'), $data);

        // Verificar que se lanza una excepción y se devuelve un código 406
        $response->assertStatus(406);
        $response->assertJson(['message' => 'No puedes matricularte en este curso']);
    }

    /** @test Error 406 Por que el status del programa es diferente a ENROLLING*/
    public function testUserprogramNotAccepted()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'ENROLLING')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid
        ])->first();


        // Agregar al usuario como estudiante pero no aprobado
        $educationalProgram->students()->attach($user->uid, ['acceptance_status' => 'REJECTED', 'uid' => generate_uuid()]);


        // Preparar los datos de la solicitud
        $data = [
            'educationalProgramUid' => $educationalProgram->uid,
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(route('enroll-educational-program-inscribed'), $data);


        // Verificar que se lanza una excepción y se devuelve un código 406
        $response->assertStatus(406);
        $response->assertJson(['message' => 'No has sido aprobado en este curso']);
    }

    /** @test */
    public function testUserEnrollFreeProgram()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'ENROLLING')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid,
            'cost' => 0,
        ])->first();

        // Agregar al usuario como estudiante pero no aprobado
        $educationalProgram->students()->attach($user->uid, ['acceptance_status' => 'ACCEPTED', 'uid' => generate_uuid()]);

        // Preparar los datos de la solicitud
        $data = [
            'educationalProgramUid' => $educationalProgram->uid,
            // 'payment_gateway' => 'gateway_test',
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(route('enroll-educational-program-inscribed'), $data);

        // Verificar que no se requiere pago y que el usuario está matriculado correctamente
        $response->assertStatus(200);
        $response->assertJson(['requirePayment' => false, 'message' => 'Matriculado en el curso correctamente']);

        // Verificar que el usuario esté matriculado en el programa educativo
        $this->assertDatabaseHas('educational_programs_students', [
            'user_uid' => $user->uid,
            'educational_program_uid' => $educationalProgram->uid,
            'status' => 'ENROLLED',
        ]);
    }

    /** @test */
    public function testUserEnrollNotFreeProgram()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'ENROLLING')->first();

        app()->instance(
            'general_options',
            [
                'payment_gateway' => true,
                'redsys_commerce_code' => true,
                'redsys_currency' => true,
                'redsys_transaction_type' => false,
                'redsys_terminal' => true,
                'redsys_encryption_key' => true,
            ]
        );

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid,
            'cost' => 100,
        ])->first();

        EducationalProgramsPaymentsModel::factory()->create(
            [
                'educational_program_uid' => $educationalProgram->uid,
                'user_uid' => $user->uid
            ]
        );

        // Agregar al usuario como estudiante pero no aprobado
        $educationalProgram->students()->attach($user->uid, ['acceptance_status' => 'ACCEPTED', 'uid' => generate_uuid()]);

        // Preparar los datos de la solicitud
        $data = [
            'educationalProgramUid' => $educationalProgram->uid,
            // 'payment_gateway' => 'gateway_test',
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(route('enroll-educational-program-inscribed'), $data);

        // Verificar que no se requiere pago y que el usuario está matriculado correctamente
        $response->assertStatus(200);
        // $response->assertJson(['requirePayment' => true, 'message' => 'Matriculado en el curso correctamente']);

        // Verificar que el usuario esté matriculado en el programa educativo
        $this->assertDatabaseHas('educational_programs_students', [
            'user_uid' => $user->uid,
            'educational_program_uid' => $educationalProgram->uid,
            // 'status' => 'ENROLLED',
        ]);
    }

      /** @test */
      public function testUserEnrollNotFreeProgramWihtoutPayment()
      {
          // Simular la autenticación del usuario
          $user = UsersModel::factory()->create();
          $this->actingAs($user);
  
          $status = EducationalProgramStatusesModel::where('code', 'ENROLLING')->first();
  
          app()->instance(
              'general_options',
              [
                  'payment_gateway' => true,
                  'redsys_commerce_code' => true,
                  'redsys_currency' => true,
                  'redsys_transaction_type' => false,
                  'redsys_terminal' => true,
                  'redsys_encryption_key' => true,
              ]
          );
  
          // Crear algunos programas formativos inscritos para el usuario
          $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
              'educational_program_status_uid' => $status->uid,
              'cost' => 100,
          ])->first();
  
          // Agregar al usuario como estudiante pero no aprobado
          $educationalProgram->students()->attach($user->uid, ['acceptance_status' => 'ACCEPTED', 'uid' => generate_uuid()]);
  
          // Preparar los datos de la solicitud
          $data = [
              'educationalProgramUid' => $educationalProgram->uid,
              // 'payment_gateway' => 'gateway_test',
          ];
  
          // Realizar una solicitud POST a la ruta definida
          $response = $this->post(route('enroll-educational-program-inscribed'), $data);
  
          // Verificar que no se requiere pago y que el usuario está matriculado correctamente
          $response->assertStatus(200);
          // $response->assertJson(['requirePayment' => true, 'message' => 'Matriculado en el curso correctamente']);
  
          // Verificar que el usuario esté matriculado en el programa educativo
          $this->assertDatabaseHas('educational_programs_students', [
              'user_uid' => $user->uid,
              'educational_program_uid' => $educationalProgram->uid,
              // 'status' => 'ENROLLED',
          ]);
      }   


    /** @test download documento programa*/
    public function testDownloadsDocumentProgram()
    {
        // Simular la autenticación del usuario
        $user = UsersModel::factory()->create();
        $this->actingAs($user);

        $status = EducationalProgramStatusesModel::where('code', 'ENROLLING')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid
        ])->first();

        $document_program = EducationalProgramsDocumentsModel::create([
            'uid' => generate_uuid(),
            'document_name' => 'document_name',
            'educational_program_uid' => $educationalProgram->uid,
        ])->first();


        // Crear un documento educativo para el programa
        $document = EducationalProgramsStudentsDocumentsModel::create([
            'uid' => generate_uuid(),
            'document_path' => 'storage/app/files_dowloaded_temp/document.pdf',
            'user_uid' => $user->uid,
            'educational_program_document_uid' => $document_program->uid,
        ])->first();

        // Preparar los datos de la solicitud con el UID del documento
        $data = [
            'educational_program_document_uid' => $document->uid,
        ];

        // Realizar una solicitud POST a la ruta definida
        $response = $this->post(url('/profile/my_educational_programs/inscribed/download_document_educational_program'), $data);

        // Verificar que la respuesta sea exitosa (código 200)
        $response->assertStatus(200);

        // Verificar que se devuelva un token en la respuesta
        $response->assertJsonStructure(['token']);

        // Verificar que se haya creado un token en la base de datos
        $this->assertDatabaseHas('backend_file_download_tokens', [
            'file' => $document->document_path,
        ]);
    }

    /** @test CAncelar inscripción*/
    public function testError406CancelInscription()
    {
        // Crea un usuario simulado
        $user = UsersModel::factory()->create();
        Auth::login($user);

        $status = EducationalProgramStatusesModel::where('code', 'ENROLLING')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid
        ])->first();

        // Crea un programa educativo inscrito para el usuario
        $educationalProgramStudent = EducationalProgramsStudentsModel::factory()->create([
            'user_uid' => $user->uid,
            'educational_program_uid' => $educationalProgram->uid,
            'status' => 'INSCRIBED',
        ])->first();

        // Realiza la solicitud POST para cancelar la inscripción
        $response = $this->postJson(url('/profile/my_educational_programs/inscribed/cancel_inscription'), [
            'educationalProgramUid' =>  $educationalProgramStudent->uid,
        ]);

        // Verifica que la respuesta sea correcta
        $response->assertStatus(406)
            ->assertJson(['message' => 'No estás inscrito en este programa formativo']);
    }

    public function testCancelInscription()
    {
        // Crea un usuario simulado
        $user = UsersModel::factory()->create();
        Auth::login($user);

        $status = EducationalProgramStatusesModel::where('code', 'INSCRIPTION')->first();

        // Crear algunos programas formativos inscritos para el usuario
        $educationalProgram = EducationalProgramsModel::factory()->withEducationalProgramType()->create([
            'educational_program_status_uid' => $status->uid
        ])->first();

        // Crea un programa educativo inscrito para el usuario
        $educationalProgramStudent = EducationalProgramsStudentsModel::factory()->create([
            'user_uid' => $user->uid,
            'educational_program_uid' => $educationalProgram->uid,
            'status' => 'INSCRIBED',
        ])->first();

        // Realiza la solicitud POST para cancelar la inscripción
        $response = $this->postJson(url('/profile/my_educational_programs/inscribed/cancel_inscription'), [
            'educationalProgramUid' =>  $educationalProgram->uid,
        ]);

        // Verifica que la respuesta sea correcta
        $response->assertStatus(200)
            ->assertJson(['message' => 'Inscripción cancelada correctamente']);
    }

    /**
     * @test
     * Prueba que guarda correctamente los documentos del programa educativo.
     */
    public function testSaveDocumentsEducationalProgram()
    {

        // Buscamos un usuario  
        $user = UsersModel::where('email', 'admin@admin.com')->first();
        // Si no existe el usuario lo creamos
        if (!$user) {
            $user = UsersModel::factory()->create([
                'email' => 'admin@admin.com'
            ])->first();
        }
        // Lo autenticarlo         
        $this->actingAs($user);

        // Crear un archivo simulado
        Storage::fake('documents');
        $file = UploadedFile::fake()->create('document.pdf', 100);

        // Mockear la respuesta del backend
        $filePath = 'documents/document.pdf';
        // $this->mockFunction('sendFileToBackend', ['file_path' => $filePath]);

        $educationalDocument = EducationalProgramsDocumentsModel::factory()->withEducationalProgram()->create()->first();

        // Preparar los datos de la solicitud
        $requestData = [
            $educationalDocument->uid => $file,
        ];

        // Hacer la solicitud POST a la ruta
        $response = $this->postJson('/profile/my_educational_programs/save_documents_educational_program', $requestData);


        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Documentos guardados correctamente']);
    }
}
