<?php


namespace App\Traits;


    __defifndef('RENDER_REGEX_TEMPLATE', '/\s*@template\s+(.*)*\s*/');
    __defifndef('RENDER_REGEX_PARTIAL', '/\s*@partial\s+(.*)*\s*/');
    __defifndef('RENDER_REGEX_SECTION', '/\s*@section\s+[a-zA-Z0-9_][a-zA-A0-9_]*\s*/');
    __defifndef('RENDER_REGEX_ENDSECTION', '/\s*@end\s*/');
    __defifndef('RENDER_REGEX_EXPANDSECTION', '/\s*{{[a-zA-Z0-9_][a-zA-Z0-9_]*}}\s*/');
    __defifndef('RENDER_REGEX_APPDEFINITION', '/\s*\[\[APP_[a-zA-Z0-9_]*\]\]/');
    __defifndef('RENDER_REGEX_CSSBLOCK', '/\s*\[CSS\[\s*\\n/');
    __defifndef('RENDER_REGEX_JSBLOCK', '/\s*\[JS\[\s*\\n/');
    __defifndef('RENDER_REGEX_PUBLICPATH', '/\s*\[PUBLIC\[[a-zA-Z0-9\.\/][a-zA-Z0-9_\.\/]*\]\]\s*/');
    __defifndef('RENDER_REGEX_STOREPATH', '/\s*\[STORE\[[a-zA-Z0-9\.\/][a-zA-Z0-9_\.\/]*\]\]\s*/');


    __defifndef('PATH_PUBLIC', __buildpath(Array(
        PATH_ROOT, "front", "public"
    )));

    
    __defifndef('PATH_STORE', __buildpath(Array(
        PATH_ROOT, "store"
    )));


Trait Regexp
{


private function __parseRenderStream($filename, $stream, $section)
    {
        while (! feof($stream))
        {
            if ($this->isError() !== false)
                return false;

            $_line = fgets($stream);

            if (substr(trim($_line), 0, 1) == '/')
                continue;

            if ($this->__isTemplateDirective($_line, $filename) === true)
                continue;

            if ($this->__isPartialDirective($_line, $filename, $section) === true)
                continue;

            if ($this->__isSectionDirective($_line, $filename, $section) === true)
                continue;

            if ($this->__isSectionEndDirective($_line, $filename, $section) === true)
                continue;

            if ($this->__isCSSBlock($stream, $_line, $filename) === true)
                continue;
            
            if ($this->__isJSBlock($stream, $_line, $filename) === true)
                continue;

            if (! isset($this->_sections[$section]))
                $this->_sections[$section] = "";

            $this->_sections[$section] .= $_line;
        }

        return true;
    }


private function __isTemplateDirective($line, $filename)
    {
        if (preg_match(RENDER_REGEX_TEMPLATE, $line, $match) == 1)
        {
            $_path = __buildpath(Array(
                trim(getcwd()), trim(substr(trim($match[0]), 10))
            ));

            $this->_template_path = $_path;

            return true;
        }

        return false;
    }


private function __isPartialDirective($line, $filename, &$section)
    {
        if (preg_match(RENDER_REGEX_PARTIAL, $line, $match) == 1)
        {
            $_path = __buildpath(Array(
                trim(getcwd()), trim(substr(trim($match[0]), 9))
            ));

            if ($this->__renderPage($_path, $section) === false)
                return false;

            return true;
        }

        return false;
    }


private function __isSectionDirective($line, $filename, &$section)
    {
        if (preg_match(RENDER_REGEX_SECTION, $line, $match) == 1)
        {
            if ($section !== RENDER_BODY)
                return $this->_setError("Error in file $filename - cannot have a section within a section");
    
            $section = trim(substr($match[0], 8));

            return true;
        }

        return false;
    }


private function __isSectionEndDirective($line, $filename, &$section)
    {
        if (preg_match(RENDER_REGEX_ENDSECTION, $line, $match) == 1)
        {
            if ($section === RENDER_BODY)
                return $this->_setError("Error in $filename - @end not part of any section");
    
            $section = RENDER_BODY;

            return true;
        }

        return false;
    }


private function __expandSections(&$template)
    {
        while (true)
        {
            if ($this->__isSectionIdentifier($template) === true)
                continue;

            if ($this->__isAPPIdentifier($template) === true)
                continue;
        
            if ($this->__isPublicPath($template) === true)
                continue;

            if ($this->__isStorePath($template) === true)
                continue;

            break;
        }

        $template = str_replace(']CSS]', '', $template);
        $template = str_replace(']JS]', '', $template);

        $template = str_replace('[[CSS]]', $this->_css_data, $template);
        $template = str_replace('[[JS]]', $this->_js_data, $template);

        return true;
    }


private function __isSectionIdentifier(&$template)
    {
        if (preg_match(RENDER_REGEX_EXPANDSECTION, $template, $match) == 1)
        {
            $_section = substr(trim($match[0]), 2, (strlen(trim($match[0])) - 4));

            if (! isset($this->_sections[$_section]))
                return $this->_setError("Error - reference to unknown section $_section");

            $template = str_replace($match, $this->_sections[$_section], $template);

            return true;
        }

        return false;
    }


private function __isAppIdentifier(&$template)
    {
        if (preg_match(RENDER_REGEX_APPDEFINITION, $template, $match) == 1)
        {
            $_app_def = substr(trim($match[0]), 2, (strlen(trim($match[0])) - 4));

            if (! defined($_app_def))
                return $this->_setError("Error - reference to non-existing APP_ definition $_app_def");
        
            $template = str_replace($match[0], constant($_app_def), $template);

            return true;
        }

        return false;
    }


private function __isCSSBlock(&$stream, $line, $filename)
    {
        $_css_block = "";

        if (preg_match(RENDER_REGEX_CSSBLOCK, $line, $match) == 1)
        {
            while (! feof($stream))
            {
                $_line = fgets($stream);

                if (trim($_line) == "]CSS]")
                    break;

                $_css_block .= $_line;
            }   

            $_php_out = $this->__getPHPOutput($_css_block);
            $this->_css_data .= $_php_out;

            return true;
        }

        return false;
    }


private function __isJSBlock(&$stream, $line, $filename)
    {
        $_js_block = "";

        if (preg_match(RENDER_REGEX_JSBLOCK, $line, $match) == 1)
        {
            while (! feof($stream))
            {
                $_line = fgets($stream);

                if (trim($_line) == "]JS]")
                    break;

                $_js_block .= $_line;
            }   

            $_php_out = $this->__getPHPOutput($_js_block);
            $this->_js_data .= $_php_out;
            
            return true;
        }

        return false;
    }
    

private function __isPublicPath(&$template)
    {
        if (preg_match(RENDER_REGEX_PUBLICPATH, $template, $match) == 1) {
            $_path = substr(trim($match[0]), 8, (strlen(trim($match[0])) - 10));
            $_path = __buildpath(Array(PATH_PUBLIC, $_path));

            $template = str_replace($match[0], $_path, $template);

            return true;
        }

        return false;
    }


private function __isStorePath(&$template)
    {
        if (preg_match(RENDER_REGEX_STOREPATH, $template, $match) == 1) {
            $_path = substr(trim($match[0]), 7, (strlen(trim($match[0])) - 9));
            $_path = __buildpath(Array(PATH_STORE, $_path));

            $template = str_replace($match[0], $_path, $template);

            return true;
        }

        return false;
    }

}

