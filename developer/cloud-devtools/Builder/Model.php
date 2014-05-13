<?php
Class ModelBuilder
{
    protected $_module, $_model, $_table, $_path; 
    protected $_templates = array();
    protected $_mode      = "create";
    protected $_beginToken= "##!generated";
    protected $_endToken  = "##!end-generated";
    protected $_db        = null;
    public function __construct($module, $model, $table, $path)
    {
        $this->_db     = $this->_getDb(); 
        $this->_module = $module;
        $this->_model  = $model;
        $this->_table  = $table;
        $this->_path   = $path; 
        $this->_templates = array(
        	"class"          => file_get_contents(BUILDER_PATH . DS . "Model/class.inc"),
            "variable"       =>  '/** @var {{type}} */ public ${{column_name}};',
            "class_interior" => file_get_contents(BUILDER_PATH . DS . "Model/class_interior.inc")
        );
    }
    
    public function build()
    {
        $current_file = file_get_contents($this->_path); 
        if (strstr($current_file, "Class")) {
            $this->_mode = "edit";
            if (!strstr($current_file, $this->_beginToken) || !strstr($current_file, $this->_endToken)) {
                throw new \Exception("Found an existing class file at the given location, but no generated block. Please add in {$this->_beginToken} and $this->_endToken");
            }
        }
        if ($this->_mode == "create") {
            $_map = array(
            	"beginToken" => $this->_beginToken,
                "endToken"   => $this->_endToken,
                "namespace"  => $this->_getModelNamespace(),
                "class"      => $this->_getModelClass(),
                "table"      => $this->_getModelTable(),
                "variables"  => $this->_getModelVariables(),
                "functions"  => $this->_getModelFunctions()
            );
            $_map["class_interior"] = $this->_getClassInterior($_map); 
            $_classTemplate = $this->_templates["class"];
            $_compiled      = "<?php \n".$this->_processTemplate($_classTemplate, array_keys($_map), $_map);
            file_put_contents($this->_path, $_compiled); 
        } else {
            $_map = array(
            	"variables" => $this->_getModelVariables(),
                "functions" => $this->_getModelFunctions(),
                "beginToken"=> $this->_beginToken,
                "endToken"  => $this->_endToken
            );
            $_map["class_interior"] = $this->_getClassInterior($_map);
            $_existing    = file_get_contents($this->_path);
            $beginning    = strpos($_existing, $this->_beginToken);
            $ending       = strpos($_existing, $this->_endToken) + strlen($this->_endToken);
            $_preCompiled = substr($_existing,0,$beginning)."{{class_interior}}" . substr($_existing, $ending);
            $_compiled    = $this->_processTemplate($_preCompiled, array_keys($_map), $_map);
            file_put_contents($this->_path, $_compiled);
        }
        return true; 
    }
    
    protected function _getModelVariables()
    {
        $name       = $this->_table;
        $result_set = $this->_db->query("DESC {$name}");
        $result_set->setFetchMode(Phalcon\Db::FETCH_ASSOC);
        $columns    = $result_set = $result_set->fetchAll($result_set);
        $vars       = array();
        foreach($columns as $column) 
        {
            $vars[] = array(
            	"column_name" => $column["Field"],
                "type"        => $this->_getColumnType($column["Type"])
            );
        }
        $processed_variables = array(); 
        $template            = $this->_templates["variable"];
        $map                 = array(
        	"type", "column_name"
        );
        foreach($vars as $var) {
            $processed_variables[] = $this->_processTemplate($template, $map, $var);
        }
        return implode(" ", $processed_variables);
    }
    
    protected function _getClassInterior($map)
    {
        return $this->_processTemplate($this->_templates["class_interior"], array_keys($map), $map);
    }
    
    /**
     * @todo Once more functions are added this section will need to be refactored
     * @return string
     */
    protected function _getModelFunctions()
    {
        return "/**
     * Get the table source for this model
     * @return string
     */
    public function getSource()
    {
        return '{$this->_table}';
    }";
    }
    
    protected function _processTemplate($template, $map, $data)
    {
        foreach($map as $key)
        {
            $template = str_replace("{{" . $key . "}}", $data[$key], $template);
        }
        return $template;
    }
    
    protected function _getModelTable()
    {
        return $this->_table;
    }
    
    protected function _getModelNamespace()
    {
        $namespace = "Cloud\\" . $this->_module . "\\Model";
        $model_full= $this->_model;
        $parts     = explode("\\", $model_full);
        array_pop($parts);
        $namespace .= "\\" . implode("\\", $parts);
        return $namespace;
    }
    
    protected function _getModelClass()
    {
        $model_full= $this->_model;
        $parts     = explode("\\", $model_full);
        return array_pop($parts);
    }
    
    protected function _getColumnType($column_type)
    {
        $_map = array(
        	"int"   => array("int"),
            "float" => array("float", "double", "decimal"),
            "string"=> array("text", "blob", "char", "date", "time", "year", "enum")
        );
        foreach($_map as $php_type => $mysql_types) {
            foreach($mysql_types as $mysql_type) {
                if (strstr($column_type, $mysql_type))
                    return $php_type;
            }
        }
        return "string";
    }
    
    protected function _getDb()
    {
        $_db = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        	"host" => "localhost",
            "username" => "root",
            "password" => "Aopen2close",
            "dbname"   => "cloud9living_rewrite"
        ));
        $_db->connect();
        return $_db;
    }
}