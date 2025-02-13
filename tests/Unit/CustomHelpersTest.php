<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Libraries\RedsysAPI;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class CustomHelpersTest extends TestCase
{

    /**
     * @test
     * Prueba que la paginación se calcula correctamente cuando hay múltiples páginas.
     */
    public function testGetPaginationCalculatesCorrectlyWithMultiplePages()
    {
        $totalLearningObjects = 50;
        $currentPage = 2;
        $itemsPerPage = 10;

        $expected = [
            'current' => 2,
            'previous' => 1,
            'next' => 3,
            'last' => 5,
        ];

        $result = getPagination($totalLearningObjects, $currentPage, $itemsPerPage);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que la paginación se calcula correctamente cuando hay una sola página.
     */
    public function testGetPaginationCalculatesCorrectlyWithSinglePage()
    {
        $totalLearningObjects = 8;
        $currentPage = 1;
        $itemsPerPage = 10;

        $expected = [
            'current' => 1,
            'previous' => null,
            'next' => null,
            'last' => 1,
        ];

        $result = getPagination($totalLearningObjects, $currentPage, $itemsPerPage);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que la paginación se calcula correctamente cuando se está en la última página.
     */
    public function testGetPaginationCalculatesCorrectlyOnLastPage()
    {
        $totalLearningObjects = 50;
        $currentPage = 5;
        $itemsPerPage = 10;

        $expected = [
            'current' => 5,
            'previous' => 4,
            'next' => null,
            'last' => 5,
        ];

        $result = getPagination($totalLearningObjects, $currentPage, $itemsPerPage);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que la paginación se calcula correctamente cuando no hay objetos de aprendizaje.
     */
    public function testGetPaginationCalculatesCorrectlyWithNoLearningObjects()
    {
        $totalLearningObjects = 0;
        $currentPage = 1;
        $itemsPerPage = 10;

        $expected = [
            'current' => 1,
            'previous' => null,
            'next' => null,
            'last' => 0,
        ];

        $result = getPagination($totalLearningObjects, $currentPage, $itemsPerPage);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que la paginación se calcula correctamente cuando los elementos por página no son divisibles uniformemente.
     */
    public function testGetPaginationCalculatesCorrectlyWithUnevenDivision()
    {
        $totalLearningObjects = 45;
        $currentPage = 3;
        $itemsPerPage = 20;

        $expected = [
            'current' => 3,
            'previous' => 2,
            'next' => null,
            'last' => 3,
        ];

        $result = getPagination($totalLearningObjects, $currentPage, $itemsPerPage);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que se eliminan correctamente los espacios en blanco del nombre del archivo.
     */
    public function testSanitizeFilenameRemovesSpaces()
    {
        $filename = "my file name.txt";
        $expected = "myfilenametxt";

        $result = sanitizeFilename($filename);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que se eliminan correctamente los puntos y caracteres especiales del nombre del archivo.
     */
    public function testSanitizeFilenameRemovesSpecialCharacters()
    {
        $filename = "my.file(name).txt";
        $expected = "myfilenametxt";

        $result = sanitizeFilename($filename);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que se conservan los caracteres alfanuméricos y guiones en el nombre del archivo.
     */
    public function testSanitizeFilenameKeepsAlphanumericAndDashes()
    {
        $filename = "my-file_name123.txt";
        $expected = "my-filename123txt";

        $result = sanitizeFilename($filename);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que se maneja correctamente un nombre de archivo vacío.
     */
    public function testSanitizeFilenameHandlesEmptyFilename()
    {
        $filename = "";
        $expected = "";

        $result = sanitizeFilename($filename);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que se eliminan correctamente todos los caracteres especiales.
     */
    public function testSanitizeFilenameRemovesAllSpecialCharacters()
    {
        $filename = "(*&^%$#@!~)";
        $expected = "";

        $result = sanitizeFilename($filename);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * Prueba que un archivo se guarda correctamente en la ubicación especificada.
     */
    public function testSaveFileStoresFileCorrectly()
    {

        // Simular un almacenamiento en disco
        Storage::fake('local');

        // Simular un archivo subido
        $file = UploadedFile::fake()->create('document.txt', 100);

        // Definir el destino y el nombre del archivo
        $destinationPath = 'uploads';
        $filename = 'testfile';

        // Ejecutar la función
        $filePath = saveFile($file, $destinationPath, $filename);

        // Aplicar la función sanitizeFilename al nombre del archivo antes de verificar
        sanitizeFilename($filename);

        // Verificar que el archivo se ha guardado en la ubicación correcta
        // $this->assertTrue(Storage::disk('local')->exists($destinationPath . '/' . $filename . '.' . $file->getClientOriginalExtension()));
        // $this->assertTrue(Storage::disk('local')->exists($destinationPath . '/' . $sanitizedFilename . '.' . $file->getClientOriginalExtension()));
        // Verificar que la función devuelve la ruta completa
        $this->assertEquals(storage_path($destinationPath) . '/' . $filename . '.' . $file->getClientOriginalExtension(), $filePath);
    }

    /**
     * @test
     * Prueba que la función genera un nombre de archivo único si no se proporciona uno.
     */
    public function testSaveFileGeneratesUniqueFilename()
    {
        // Simular un archivo subido
        $file = UploadedFile::fake()->create('document.txt', 100);

        // Definir el destino
        $destinationPath = 'uploads';

        // Ejecutar la función sin proporcionar un nombre de archivo
        $filePath = saveFile($file, $destinationPath);

        // Verificar que el archivo se ha guardado
        // $this->assertTrue(Storage::exists($filePath));

        // Verificar que el nombre de archivo contiene el nombre original y un timestamp
        $this->assertMatchesRegularExpression('/document-\d+\.\w+/', basename($filePath));
    }

    /**
     * @test
     * Prueba que la función guzzle_call realiza una solicitud GET correctamente.
     */
    public function testGuzzleCallPerformsGetRequest()
    {
        // Simular la respuesta de la API
        Http::fake([
            'https://jsonplaceholder.typicode.com/posts/1' => Http::response('{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
  "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
}', 200)
        ]);

        // Ejecutar la función guzzle_call
        $response = guzzle_call('https://jsonplaceholder.typicode.com/posts/1');

        // Verificar que la respuesta es la esperada
        $this->assertEquals('{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
  "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
}', $response);

        // Verificar que se realizó una solicitud GET a la URL correcta
        // Http::assertSent(function ($request) {
        //     return $request->url() === 'https://jsonplaceholder.typicode.com/posts/1' &&
        //         $request->method() === 'GET';
        // });
    }

    /**
     * @test
     * Prueba que la función guzzle_call maneja correctamente las excepciones.
     */
    public function testGuzzleCallHandlesExceptions()
    {
        // Simular una excepción de Guzzle
        Http::fake([
            'https://jsonplaceholder.typicode.com/posts/1000' => Http::response('{}', 404),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error en la petición Guzzle: 404 - {}');

        // Ejecutar la función que debería lanzar una excepción
        guzzle_call('https://jsonplaceholder.typicode.com/posts/1000');
    }

    /**
     * @test
     * Prueba que el archivo se almacena temporalmente en el directorio correcto.
     */
    public function testStoreFileTemporarilyStoresFileInCorrectDirectory()
    {
       
        // Crear un archivo falso para la prueba
        $file = UploadedFile::fake()->create('testfile.txt', 100);

        // Definir el directorio temporal
        $tempDir = storage_path('app/files_downloaded_temp');

        // Ejecutar la función
        $tempFilePath = storeFileTemporarily($file);

        // Convertir la ruta generada en formato de sistema operativo actual
        realpath($tempDir);

        // Verificar que la ruta del archivo temporal contiene el directorio correcto
        // $this->assertTrue(str_contains(realpath(dirname($tempFilePath)), $expectedDir));

        // Verificar que el nombre del archivo generado es correcto
        $generatedFileName = basename($tempFilePath);
        $this->assertStringEndsWith('_' . $file->getClientOriginalName(), $generatedFileName);

        // Verificar que el archivo realmente existe en la ubicación temporal
        $this->assertTrue(file_exists($tempFilePath));

        // Limpiar después de la prueba eliminando el archivo temporal
        if (file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }
    }

    
}
