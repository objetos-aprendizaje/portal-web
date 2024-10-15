<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Libraries\RedsysAPI;

class RedsysAPITest extends TestCase
{
    protected $redsysAPI;

    protected function setUp(): void
    {
        parent::setUp();
        // Inicializa la instancia de RedsysAPI
        $this->redsysAPI = new RedsysAPI();

        /// Configura vars_pay con valores por defecto
        $this->redsysAPI->vars_pay = [
            'Ds_Order' => '12345',
            'DS_ORDER' => '67890',
            // Agrega otros parámetros si es necesario
        ];
    }

    /**
     * @test
     * Prueba que el método getParameter devuelve el valor correcto para una clave dada.
     */
    public function testCanBaseObtenerParametro()
    {
        // Definir la clave y el valor esperado
        $key = 'Ds_Order';
        $expectedValue = '12345';

        // Verificar que el método getParameter devuelva el valor correcto
        $this->assertEquals($expectedValue, $this->redsysAPI->getParameter($key));
    }

    /**
     * @test
     * Prueba que el método base64_url_encode codifica correctamente una cadena.
     */
    public function testCanBase64UrlCodificar()
    {
        // Definir la entrada y la salida esperada
        $input = 'Hello World!';
        $expectedOutput = 'SGVsbG8gV29ybGQh'; // Codificación Base64 de 'Hello World!'

        // Verificar que el método base64_url_encode codifique correctamente
        $this->assertEquals($expectedOutput, $this->redsysAPI->base64_url_encode($input));
    }

    /**
     * @test
     * Prueba que el método base64_url_decode decodifica correctamente una cadena previamente codificada.
     */
    public function testCanBase64UrlDecodificar()
    {
        // Definir la entrada y la salida esperada
        $input = 'SGVsbG8gV29ybGQh'; // Codificación Base64 de 'Hello World!'
        $expectedOutput = 'Hello World!';

        // Verificar que el método base64_url_decode decodifique correctamente
        $this->assertEquals($expectedOutput, $this->redsysAPI->base64_url_decode($input));
    }

    /**
     * @test
     * Prueba que el método getOrderNotif devuelve el número de pedido correcto.
     */
    public function testCanBaseObtenerNumeroDePedido()
    {
        // Verificar que se obtenga el número de pedido correcto
        $this->assertEquals('12345', $this->redsysAPI->getOrderNotif());

        // Cambiar vars_pay para probar la otra condición
        $this->redsysAPI->vars_pay['Ds_Order'] = '';
        $this->assertEquals('67890', $this->redsysAPI->getOrderNotif());
    }

    /**
     * @test
     * Prueba que el método getOrderNotifSOAP extrae correctamente el número de pedido desde una cadena XML.
     */
    public function testCanBaseObtenerNumeroDePedidoSOAP()
    {
        $datos = '<Response><Ds_Order>12345</Ds_Order></Response>';
        $expectedOutput = '12345';

        $this->assertEquals($expectedOutput, $this->redsysAPI->getOrderNotifSOAP($datos));
    }

    /**
     * @test
     * Prueba que el método getRequestNotifSOAP extrae correctamente la solicitud desde una cadena XML.
     */
    public function testCanBaseObtenerSolicitudSOAP()
    {
        $datos = '<Request><Ds_Order>12345</Ds_Order></Request>';
        $expectedOutput = '<Request><Ds_Order>12345</Ds_Order></Request>';

        $this->assertEquals($expectedOutput, $this->redsysAPI->getRequestNotifSOAP($datos));
    }

    /**
     * @test
     * Prueba que el método getResponseNotifSOAP extrae correctamente la respuesta desde una cadena XML.
     */
    public function testCanBaseObtenerRespuestaSOAP()
    {
        $datos = '<Response><Status>OK</Status></Response>';
        $expectedOutput = '<Response><Status>OK</Status></Response>';

        $this->assertEquals($expectedOutput, $this->redsysAPI->getResponseNotifSOAP($datos));
    }

    /**
     * @test
     * Prueba que el método stringToArray convierte correctamente un string JSON en un array.
     */
    public function testCanBaseConvertirStringAArray()
    {
        $jsonString = '{"key1":"value1","key2":"value2"}';

        // Convertir el string JSON a array
        $this->redsysAPI->stringToArray($jsonString);

        // Verificar que vars_pay contenga los valores correctos
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $this->redsysAPI->vars_pay);
    }

    /**
     * @test
     * Prueba que el método decodeMerchantParameters decodifica correctamente los parámetros del comerciante.
     */
    public function testCanBaseDecodificarParametrosDelComerciante()
    {
        // Simular datos codificados en Base64 (ejemplo)
        $data = base64_encode(json_encode(['key1' => 'value1', 'key2' => 'value2']));

        // Decodificar y verificar que se almacenen correctamente en vars_pay
        $decodedData = $this->redsysAPI->decodeMerchantParameters($data);

        // Verificar que los datos decodificados sean correctos
        $this->assertEquals('{"key1":"value1","key2":"value2"}', $decodedData);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $this->redsysAPI->vars_pay);
    }

    // /**
    //  * @test
    //  * Prueba que el método createMerchantSignatureNotif genera correctamente la firma del comerciante.
    //  */
    // public function testCanBaseCrearFirmaDelComercianteNotif()
    // {
    //     // Definir clave y datos de prueba
    //     $key = base64_encode('mi_clave_secreta'); // Clave en Base64
    //     $datos = base64_encode(json_encode(['param1' => 'value1', 'param2' => 'value2'])); // Datos en Base64

    //     // Asegúrate de que vars_pay tenga las claves necesarias
    //     $this->redsysAPI->vars_pay = [
    //         'Ds_Order' => '12345',  // Clave utilizada en getOrderNotif
    //         'DS_ORDER' => '67890',   // Clave utilizada como respaldo
    //     ];

    //     // Llamar al método y obtener la firma generada
    //     $firmaGenerada = $this->redsysAPI->createMerchantSignatureNotif($key, $datos);

    //     // Verificar que la firma generada no sea nula o vacía
    //     $this->assertNotEmpty($firmaGenerada);

    //     // Aquí puedes agregar más aserciones específicas basadas en tu lógica de negocio.
    // }

    /**
     * @test
     * Prueba que el método createMerchantSignatureNotifSOAPRequest genera correctamente la firma del comerciante para solicitudes SOAP.
     */
    public function testCanBaseCrearFirmaDelComercianteSOAPRequest()
    {
        // Definir clave y datos de prueba
        $key = base64_encode('mi_clave_secreta'); // Clave en Base64
        $datos = '<Request><Ds_Order>12345</Ds_Order></Request>'; // Datos en formato XML

        // Llamar al método y obtener la firma generada
        $firmaGenerada = $this->redsysAPI->createMerchantSignatureNotifSOAPRequest($key, $datos);

        // Verificar que la firma generada no sea nula o vacía
        $this->assertNotEmpty($firmaGenerada);

        // Aquí puedes agregar más aserciones específicas basadas en tu lógica de negocio.
    }

    /**
     * @test
     * Prueba que el método createMerchantSignatureNotifSOAPResponse genera correctamente la firma del comerciante para respuestas SOAP.
     */
    public function testCanBaseCrearFirmaDelComercianteSOAPResponse()
    {
        // Definir clave, datos y número de pedido de prueba
        $key = base64_encode('mi_clave_secreta'); // Clave en Base64
        $datos = '<Response><Status>OK</Status></Response>'; // Datos en formato XML
        $numPedido = '12345'; // Número de pedido

        // Llamar al método y obtener la firma generada
        $firmaGenerada = $this->redsysAPI->createMerchantSignatureNotifSOAPResponse($key, $datos, $numPedido);

        // Verificar que la firma generada no sea nula o vacía
        $this->assertNotEmpty($firmaGenerada);

        // Aquí puedes agregar más aserciones específicas basadas en tu lógica de negocio.
    }
}
