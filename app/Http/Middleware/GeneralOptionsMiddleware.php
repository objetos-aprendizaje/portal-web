<?php

namespace App\Http\Middleware;

use App\Models\FooterPagesModel;
use Closure;
use App\Models\GeneralOptionsModel;
use App\Models\HeaderPagesModel;
use App\Models\SuggestionSubmissionEmailsModel;
use Illuminate\Support\Facades\View;

/**
 * Envía a todas las vistas las opciones generales
 */
class GeneralOptionsMiddleware
{

    /**
     * Comparte con los controladores y las vistas las opciones generales
     */
    public function handle($request, Closure $next)
    {
        $generalOptions = GeneralOptionsModel::all()->pluck('option_value', 'option_name')->toArray();

        $footerPages = FooterPagesModel::all()->toArray();

        $headerPages = HeaderPagesModel::whereNull('header_page_uid')->with('headerPagesChildren')->orderBy('order', 'asc')->get();

        $fonts = $this->getFonts($generalOptions);

        app()->instance('general_options', $generalOptions);

        View::share('general_options', $generalOptions);
        View::share('fonts', $fonts);

        View::share('footer_pages', $footerPages);
        View::share('header_pages', $headerPages);

        $existsEmailSuggestions = SuggestionSubmissionEmailsModel::count() ? true : false;
        app()->instance('existsEmailSuggestions', $existsEmailSuggestions);
        View::share('existsEmailSuggestions', $existsEmailSuggestions);

        return $next($request);
    }

    private function getFonts($generalOptions)
    {

        $regularFonts = $this->getRegularFonts($generalOptions);
        $mediumFonts = $this->getMediumFonts($generalOptions);
        $boldFonts = $this->getBoldFonts($generalOptions);

        // Concatenamos las fuentes
       return array_merge($regularFonts, $mediumFonts, $boldFonts);

    }

    private function getRegularFonts($generalOptions) {
        // Filtramos las opciones generales sólo por las fuentes regulares
        $regularFontsKeys = [
            'truetype_regular_file_path',
            'woff_regular_file_path',
            'woff2_regular_file_path',
            'embedded_opentype_regular_file_path',
            'opentype_regular_input_file',
            'svg_regular_file_path'
        ];
        $arrayRegularFonts = array_intersect_key($generalOptions, array_flip($regularFontsKeys));

        // Si hay al menos una fuente regular definida, nos quedamos con esa y ponemos el resto a null
        $existFontDefined = !empty(array_filter($arrayRegularFonts));

        $regularFonts = [];
        if($existFontDefined) {

            foreach($arrayRegularFonts as $key => $value) {
                $regularFonts[$key] = $value ? env('BACKEND_URL') . '/' . $value : null;
            }
        }
        // Si no, nos quedamos con las fuentes por defecto
        else {
            $regularFonts = [
                'truetype_regular_file_path' => "/fonts/Roboto-Regular/Roboto-Regular.ttf",
                'woff_regular_file_path' => "/fonts/Roboto-Regular/Roboto-Regular.woff",
                'woff2_regular_file_path' => "/fonts/Roboto-Regular/Roboto-Regular.woff2",
                'embedded_opentype_regular_file_path' => "/fonts/Roboto-Regular/Roboto-Regular.eot",
                'opentype_regular_input_file' => "/fonts/Roboto-Regular/Roboto-Regular.otf",
                'svg_regular_file_path' => "/fonts/Roboto-Regular/Roboto-Regular.svg"
            ];
        }

        return $regularFonts;

    }

    private function getMediumFonts($generalOptions) {
        // Filtramos las opciones generales sólo por las fuentes medias
        $mediumFontsKeys = [
            'truetype_medium_file_path',
            'woff_medium_file_path',
            'woff2_medium_file_path',
            'embedded_opentype_medium_file_path',
            'opentype_medium_file_path',
            'svg_medium_file_path'
        ];
        $arrayMediumFonts = array_intersect_key($generalOptions, array_flip($mediumFontsKeys));

        // Si hay al menos una fuente media definida, nos quedamos con esa y ponemos el resto a null
        $existFontDefined = !empty(array_filter($arrayMediumFonts));

        $mediumFonts = [];
        if($existFontDefined) {

            foreach($arrayMediumFonts as $key => $value) {
                $mediumFonts[$key] = $value ? env('BACKEND_URL') . '/' . $value : null;
            }

        }
        // Si no, nos quedamos con las fuentes por defecto
        else {
            $mediumFonts = [
                'truetype_medium_file_path' => "/fonts/Roboto-Medium/Roboto-Medium.ttf",
                'woff_medium_file_path' => "/fonts/Roboto-Medium/Roboto-Medium.woff",
                'woff2_medium_file_path' => "/fonts/Roboto-Medium/Roboto-Medium.woff2",
                'embedded_opentype_medium_file_path' => "/fonts/Roboto-Medium/Roboto-Medium.eot",
                'opentype_medium_file_path' => "/fonts/Roboto-Medium/Roboto-Medium.otf",
                'svg_medium_file_path' => "/fonts/Roboto-Medium/Roboto-Medium.svg"
            ];
        }

        return $mediumFonts;
    }

    private function getBoldFonts($generalOptions) {
        // Filtramos las opciones generales sólo por las fuentes negritas
        $boldFontsKeys = [
            'truetype_bold_file_path',
            'woff_bold_file_path',
            'woff2_bold_file_path',
            'embedded_opentype_bold_file_path',
            'opentype_bold_file_path',
            'svg_bold_file_path'
        ];
        $arrayBoldFonts = array_intersect_key($generalOptions, array_flip($boldFontsKeys));

        // Si hay al menos una fuente negrita definida, nos quedamos con esa y ponemos el resto a null
        $existFontDefined = !empty(array_filter($arrayBoldFonts));

        $boldFonts = [];
        if($existFontDefined) {

            foreach($arrayBoldFonts as $key => $value) {
                $boldFonts[$key] = $value ? env('BACKEND_URL') . '/' . $value : null;
            }
        }
        // Si no, nos quedamos con las fuentes por defecto
        else {
            $boldFonts = [
                'truetype_bold_file_path' => "/fonts/Roboto-Bold/Roboto-Bold.ttf",
                'woff_bold_file_path' => "/fonts/Roboto-Bold/Roboto-Bold.woff",
                'woff2_bold_file_path' => "/fonts/Roboto-Bold/Roboto-Bold.woff2",
                'embedded_opentype_bold_file_path' => "/fonts/Roboto-Bold/Roboto-Bold.eot",
                'opentype_bold_file_path' => "/fonts/Roboto-Bold/Roboto-Bold.otf",
                'svg_bold_file_path' => "/fonts/Roboto-Bold/Roboto-Bold.svg"
            ];
        }

        return $boldFonts;
    }
}
