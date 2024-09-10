<?php


namespace App\Controllers;


use App\Traits\Errors;
use App\Traits\Single;
use App\Traits\Regexp;


//  These are usually set in:
//
//      back/config/routes.config.php
//
    __defifndef('PATH_VIEWS', __buildpath(array(
        PATH_ROOT, "front", "views"
    )));

    __defifndef('PATH_ROUTES', __buildpath(Array(
        PATH_ROOT, "front", "routes"
    )));

    __defifndef('ROUTE_404', 'Pages/404.php');
    __defifndef('RENDER_BODY', 'SECTION_BODY');


Class Render
{

    use                 Errors;
    use                 Single;
    use                 Regexp;

    private static      $_instance = false;

    private             $_input_file;
    private             $_template_path;

    private             $_sections;
    private             $_page_data;

    private             $_status;

    public              $_data;

    private             $_error_message;


private function __construct()
    {
        $this->_setError();
        $this->refresh();
    }


public function refresh()
    {
        $this->_input_file = false;
        $this->_template_path = false;

        $this->_sections = Array();
        $this->_page_data = false;

        $this->_html_data = "";
        $this->_css_data = "";
        $this->_js_data = "";

        $this->_data = array();
    }


public function getPageOutput()
    {
        return Array(
            RENDER_HTML => $this->_html_data,
            RENDER_CSS => $this->_css_data,
            RENDER_JS => $this->_js_data
        );
    }


public function addSection($secton_id, $section)
    {
        $this->_sections[$section_id] = $section;
    }


public function addHTML($css_data)
    {
        $this->_css_data .= $css_data;
    }


public function addCSS($css_data)
    {
        $this->_css_data .= $css_data;
    }


public function addJS($js_data)
    {
        $this->_js_data .= $js_data;
    }


public function _renderPage($path, $data, $status)
    {
        $this->_inpu5_file = $path;
        $this->_data = $data;

        $_startdir = getcwd();

        chdir(PATH_VIEWS);

        $_path = __buildpath(Array(PATH_VIEWS, $path));
        $_section = RENDER_BODY;

        if (! is_file($_path))
        {
            $_path = __buildpath(Array(PATH_VIEWS, ROUTE_404));
            
            if (! is_file($_path))
                return $this->_setError("Error - 404 can\'t be found!");

            $status = 404;
        }
        
        $this->_status = $status;

        chdir($_startdir);

        if ($this->__renderPage($_path, $_section) === false)
            return false;

        if ($this->__compilePage() === false)
            return false;
    }


private function __renderPage($path, $section)
    {
        $_pathinfo = pathinfo($path);
        $_startdir = getcwd();

        $_dirname = $_pathinfo['dirname'];
        $_extension = "";

        if (isset($_pathinfo['extension']))
            $_extension = "." . $_pathinfo['extension'];
        
        $_filename = $_pathinfo['filename'] . $_extension;

        chdir($_dirname);
        $_file_path = __buildpath(Array(trim($_dirname), trim($_filename)));

        if (($_stream = fopen($_file_path, "r")) === false)
            return $this->_setError("Error opening view template $path");

        if ($this->__parseRenderStream($path, $_stream, $section) === false)
            return false;

        chdir($_startdir);
        fclose($_stream);

        return true;
    }


private function __compilePage()
    {
    //  If a template file is used, then it should
    //  expand RENDER_BODY (SECTION_BODY).
    //
    //  If no template file is used, then the data
    //  in SECTION_BODY is the template.
    //
        if ($this->_template_path !== false) {
            if (! is_file($this->_template_path))
                return $this->_setError("Error loading template file $this->_template_path");

            $_template = file_get_contents($this->_template_path);
        }
        else {
            if (! isset($this->_sections[RENDER_BODY]) || trim($this->_sections[RENDER_BODY]) === "")
                return $this->_setError("Error - no " . RENDER_BODY . " to render");

            $_template = $this->_sections[RENDER_BODY];
        }

        if ($this->__expandSections($_template) === false)
            return false;

        $this->_page_data = $_template;

        if ($this->__showPage() === false)
            return false;

        return true;
    }


private function __showPage()
    {
        http_response_code($this->_status);

        $_php_output = $this->__getPHPOutput($this->_page_data);

        echo $_php_output;
    }
    

private function __getPHPOutput($php_input)
    {
        ob_start();
        eval("?>" . $php_input);
        $_php_output = ob_get_contents();
        ob_end_clean();

        return $_php_output;
    }
}

