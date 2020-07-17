<?php

namespace Moxl;

class Parser
{
    private $parser;
    private $depth = 0;
    private $node = null;
    private $handler = null;
    private $raw = false;
    private $callback = null;

    public function __construct($callback)
    {
        $this->reset();
        $this->callback = $callback;
    }

    public function reset()
    {
        if ($this->parser) {
            xml_parser_free($this->parser);
        }

        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);

        xml_set_element_handler($this->parser, "start", "end");
        xml_set_character_data_handler($this->parser, "contents");
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, "UTF-8");

        libxml_use_internal_errors(true);

        $this->depth = 0;
        $this->node = $this->handler = null;
    }

    public function parse($data, $end = false)
    {
        if ('<?xml' === substr($data, 0, 5)
        || '<stream:stream' === substr($data, 0, 14)) {
            $this->reset();
        }

        $data = preg_replace('/>\s+</', "><", $data);
        return xml_parse($this->parser, $data, $end);
    }

    private function start($parser, $name, $attrs)
    {
        $name = str_replace(':', '', $name);

        if ($this->depth == 1) {
            $this->node = $this->handler = simplexml_load_string("<$name></$name>", 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
        } elseif ($this->depth > 1) {
            if ($this->raw != false) {
                $this->handler[0] .= '<'.$name.' ';
                if ($this->raw <= $this->depth) {
                    foreach ($attrs as $name => $value) {
                        $this->handler[0] .= $name."='".$value."' ";
                    }
                }
                $this->handler[0] .= '>';
            } else {
                $this->handler = $this->handler->addChild($name);
            }
        }

        if (isset($this->handler) && $this->raw == false) {
            foreach ($attrs as $name => $value) {
                if ('xmlns:' === substr($name, 0, 6)) {
                    $name = 'xmlns:'.$name;
                }
                if ($value === 'http://www.w3.org/1999/xhtml') {
                    $this->raw = $this->depth;
                }
                $this->handler->addAttribute($name, $value);
            }
        }

        $this->depth++;
    }

    private function end($parser, $name)
    {
        $name = str_replace(':', '', $name);

        $this->depth--;

        if ($this->raw != false
        && $this->depth > $this->raw) {
            $this->handler[0] .= '</'.$name.'>';
        }

        if ($this->raw != false
        && $this->depth == $this->raw) {
            $this->raw = false;
        }

        if ($this->depth == 1) {
            call_user_func_array($this->callback, [$this->node]);
        } elseif ($this->depth > 1 && $this->raw == false) {
            $this->handler = current($this->handler->xpath("parent::*"));
        }
    }

    private function contents($parser, $data)
    {
        if (isset($this->handler)) {
            $this->handler[0] .= (string)htmlentities($data, ENT_XML1, 'UTF-8', false);
        }
    }

    public function getError()
    {
        return sprintf(
            "XML error: %s at line %d, column %d\n",
            xml_error_string(xml_get_error_code($this->parser)),
            xml_get_current_line_number($this->parser),
            xml_get_current_column_number($this->parser)
        );
    }

    public function __destruct()
    {
        xml_parser_free($this->parser);
    }
}
