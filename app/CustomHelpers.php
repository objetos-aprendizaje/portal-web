<?php

use App\Exceptions\OperationFailedException;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use App\Libraries\RedsysAPI;
use Carbon\Carbon;
use App\Models\EmailVerifyModel;
use Illuminate\Support\Facades\URL;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\DB;

function generate_uuid()
{
    return (string) Str::uuid();
}

// Se agrego esta funcion por error presentado al correr las migraciones
function generateUuid()
{
    return (string) Str::uuid();
}

function generateRandomNumber($length)
{
    $min = pow(10, $length - 1);
    $max = pow(10, $length) - 1;
    return rand($min, $max);
}

function getPagination($totalLearningObjects, $currentPage = 1, $itemsPerPage = 10)
{
    // Asegúrate de que los argumentos sean enteros
    $totalLearningObjects = intval($totalLearningObjects);
    $currentPage = intval($currentPage);
    $itemsPerPage = intval($itemsPerPage);

    $totalPages = intval(ceil($totalLearningObjects / $itemsPerPage));

    // Calcula las páginas anterior y siguiente
    $previousPage = $currentPage > 1 ? $currentPage - 1 : null;
    $nextPage = $currentPage < $totalPages ? $currentPage + 1 : null;

    // Crea el array de páginas
    return [
        'current' => $currentPage,
        'previous' => $previousPage,
        'next' => $nextPage,
        'last' => $totalPages,
    ];

}

function sanitizeFilename($filename)
{
    // Eliminar espacios en blanco
    $filename = str_replace(' ', '', $filename);

    // Eliminar puntos, paréntesis y otros caracteres especiales
    $filename = preg_replace('/[^A-Za-z0-9\-]/', '', $filename);

    return $filename;
}

/**
 * Recibe un fichero, ruta y nombre (opcional) y lo guarda en la ruta especificada.
 * Devuelve la ruta completa del fichero guardado.
 */
function saveFile($file, $destinationPath, $filename = null, $public_path = false)
{
    // Si el nombre del archivo no se proporciona, generarlo
    $extension = $file->getClientOriginalExtension();

    if (!$filename) {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = sanitizeFilename($originalName);
        $timestamp = time();
        $filename = "{$originalName}-{$timestamp}.{$extension}";
    } else {
        $filename = "{$filename}.{$extension}";
    }

    // Determinar la ruta de destino
    if ($public_path) {
        public_path($destinationPath);
    } else {
        $destinationPath = storage_path($destinationPath);
    }

    // Comprobar si el directorio existe; si no, crearlo
    if (!is_dir($destinationPath)) {
        mkdir($destinationPath, 0777, true);
    }

    // Mover el archivo al directorio destino
    $file->move($destinationPath, $filename);

    // Devolver la ruta completa del archivo
    return "{$destinationPath}/{$filename}";
}
function generateToken($longitud = 64)
{
    $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ()';
    $charLong = strlen($char);
    $token = '';

    for ($i = 0; $i < $longitud; $i++) {
        $randomIndex = random_int(0, $charLong - 1);
        $token .= $char[$randomIndex];
    }

    return $token;
}

function guzzle_call($url, $data = null, $headers = null, $method = 'GET')
{
    // Inicializa Guzzle
    $client = new Client();

    // Configura las opciones de Guzzle
    $options = [];
    if ($data) {
        $options['json'] = $data;
    }

    if ($headers) {
        $options['headers'] = $headers;
    }

    try {
        // Ejecuta la petición con Guzzle
        $response = $client->request($method, $url, $options);

        // Devuelve la respuesta
        return (string) $response->getBody();
    } catch (RequestException $e) {
        if ($e->hasResponse()) {
            // Obtiene la respuesta completa
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            throw new OperationFailedException('Error en la petición Guzzle: ' . $statusCode . ' - ' . $body);
        } else {
            throw new OperationFailedException('Error en la petición Guzzle: ' . $e->getMessage());
        }
    }
}

function generateRedsysObject($amount, $orderNumber, $merchantData, $description = null, $urlOk = null, $urlKo = null)
{

    // Comprobamos si los pagos están activos
    if (!app('general_options')['payment_gateway']) {
        throw new \Exception('Ha ocurrido un error. Por favor, contacte con el administrador de la plataforma');
    }

    // Preparamos el objeto de la API de Redsys
    $miObj = new RedsysAPI;

    $amount = number_format($amount, 2, '', '');
    $miObj->setParameter("DS_MERCHANT_AMOUNT", $amount);

    $miObj->setParameter("DS_MERCHANT_ORDER", $orderNumber);
    $miObj->setParameter("DS_MERCHANT_MERCHANTCODE", app('general_options')['redsys_commerce_code']);
    $miObj->setParameter("DS_MERCHANT_CURRENCY", app('general_options')['redsys_currency']);
    $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", app('general_options')['redsys_transaction_type']);
    $miObj->setParameter("DS_MERCHANT_TERMINAL", app('general_options')['redsys_terminal']);
    $miObj->setParameter("DS_MERCHANT_MERCHANTDATA", $merchantData);

    $miObj->setParameter("DS_MERCHANT_MERCHANTURL", env('DS_MERCHANT_MERCHANTURL', route('webhook_process_payment_redsys')));

    if ($description) {
        $miObj->setParameter("DS_MERCHANT_PRODUCTDESCRIPTION", $description);
    }

    $miObj->setParameter("DS_MERCHANT_URLOK", $urlOk ?? route('index'));
    $miObj->setParameter("DS_MERCHANT_URLKO", $urlKo ?? route('error', ['code' => '001']));

    //Datos de configuración
    $version = "HMAC_SHA256_V1";
    $kc = app('general_options')['redsys_encryption_key'];

    // Se generan los parámetros de la petición
    $params = $miObj->createMerchantParameters();
    $signature = $miObj->createMerchantSignature($kc);

    return [
        'Ds_SignatureVersion' => $version,
        'Ds_MerchantParameters' => $params,
        'Ds_Signature' => $signature,
    ];
}

function formatDateTimeNotifications($date)
{
    Carbon::setLocale('es');

    $pastDate = new Carbon($date);
    $currentDate = Carbon::now();
    $interval = $currentDate->diff($pastDate);

    // Si la fecha es hoy
    if ($pastDate->isToday()) {
        if ($interval->h > 0) {
            return "Hace " . $interval->h . ($interval->h == 1 ? " hora" : " horas");
        } else {
            return "Hace " . $interval->i . ($interval->i == 1 ? " minuto" : " minutos");
        }
    }

    // Si la fecha fue durante la semana pasada
    $startOfCurrentWeek = $currentDate->copy()->startOfWeek();
    $startOfLastWeek = $currentDate->copy()->subWeek()->startOfWeek();

    if ($pastDate->greaterThanOrEqualTo($startOfLastWeek) && $pastDate->lessThan($startOfCurrentWeek)) {
        return "El " . $pastDate->isoFormat('dddd') . ", a las " . $pastDate->format('H:i');
    }

    // Si la fecha es más antigua que la semana pasada
    return $pastDate->format('d/m/Y') . " a las " . $pastDate->format('H:i');
}

function storeFileTemporarily($file)
{
    // Definir el directorio temporal donde se almacenará el archivo
    $tempDir = '/storage/app/files_downloaded_temp';

    // Verificar si el directorio existe, si no, crearlo
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Generar un nombre único para el archivo
    $tempFileName = uniqid() . '_' . $file->getClientOriginalName();

    // Mover el archivo al directorio temporal
    $tempFilePath = $tempDir . '/' . $tempFileName;
    if ($file->move($tempDir, $tempFileName)) {
        return $tempFilePath;
    } else {
        return false;
    }
}

function sendFileToBackend($file, $url, $header = [], $decode_response = true)
{
    // Inicializar cURL
    $curl = curl_init();

    // Preparar el archivo para enviarlo mediante cURL
    $cfile = new CURLFile($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName());

    // Establecer opciones de cURL
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POSTFIELDS, ['file' => $cfile]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la sesión cURL
    $response = curl_exec($curl);

    // Verificar si ocurrió algún error durante la petición
    if (curl_errno($curl)) {
        throw new OperationFailedException(curl_error($curl));
    }

    // Obtener el código de estado HTTP de la respuesta
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    // Verificar el código de estado HTTP
    if ($httpCode != 200 && $httpCode != 201) {
        throw new OperationFailedException("Unexpected response status: $httpCode. Response: $response");
    }

    // Cerrar la sesión cURL
    curl_close($curl);

    // Decodificar la respuesta si es necesario
    if ($decode_response) {
        return json_decode($response, true);
    } else {
        return $response;
    }
}

function downloadFileFromBackend($url, $params, $pathDownload, $header = [])
{
    // Inicializa cURL
    $ch = curl_init();

    // Configura la URL y otros parámetros apropiados
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    // Ejecuta la solicitud
    $response = curl_exec($ch);

    // Verifica si ocurrió algún error durante la solicitud
    if (curl_errno($ch)) {
        throw new OperationFailedException(curl_error($ch));
    }

    // Obtener el código de estado HTTP de la respuesta
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Verificar el código de estado HTTP
    if ($httpCode != 200 && $httpCode != 201) {
        throw new OperationFailedException("Unexpected response status: $httpCode. Response: $response");
    }

    // Cierra la sesión cURL
    curl_close($ch);

    // Construye la ruta completa donde se guardará el archivo
    $fullPath = $pathDownload;

    // Guarda el archivo en el directorio especificado
    file_put_contents($fullPath, $response);
    return $pathDownload;
}

function adaptDatesCourseEducationalProgram($courseOrEducationalProgram, $collection = false)
{
    $dateFields = [
        'inscription_start_date',
        'inscription_finish_date',
        'realization_start_date',
        'realization_finish_date',
        'enrolling_start_date',
        'enrolling_finish_date'
    ];

    if ($collection) {
        $courseOrEducationalProgram->transform(function ($course) use ($dateFields) {
            foreach ($dateFields as $field) {
                if (isset($course->$field)) {
                    $course->$field = Carbon::parse($course->$field)->setTimezone(env('TIMEZONE_DISPLAY'))->format('Y-m-d H:i:s');
                }
            }

            return $course;
        });
    } else {
        foreach ($dateFields as $field) {
            if (isset($courseOrEducationalProgram->$field)) {
                $courseOrEducationalProgram->$field = Carbon::parse($courseOrEducationalProgram->$field)->setTimezone(env('TIMEZONE_DISPLAY'))->format('Y-m-d H:i:s');
            }
        }
    }
}

function adaptDateTimezoneDisplay($date) {
    return Carbon::parse($date)->setTimezone(env('TIMEZONE_DISPLAY'))->format('Y-m-d H:i:s');
}

function sendEmail($user)
    {
        $token = generateToken(12);

        // Generar el enlace de verificación
        $verificationUrl = generateVerificationUrl($user->uid, $token);

        //guardar token y enviarlo en parametros para devolverlo con el botón del email
        saveEmailVerification($user->uid, $token);

        // Enviar la notificación
        sendEmailVerification($verificationUrl, $token, $user->email);
    }

function saveEmailVerification($userUid, $token)
    {

        DB::transaction(function () use ($userUid) {
            EmailVerifyModel::where('user_uid', $userUid)->delete();
        });
        $emailverify = new EmailVerifyModel();
        $emailverify->user_uid = $userUid;
        $emailverify->token = $token;
        $emailverify->expires_at = now()->addMinutes(60);
        $emailverify->save();
    }

function generateVerificationUrl($userUid, $token)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $userUid, 'token' => $token]
        );
        
    }

function sendEmailVerification($verificationUrl, $token, $userEmail)
    {
        $parameters = [
            'url' => $verificationUrl,
            'token' => $token
        ];

        dispatch(new SendEmailJob($userEmail, 'Verificación de contraseña', $parameters, 'emails.email_verify'));
    }
