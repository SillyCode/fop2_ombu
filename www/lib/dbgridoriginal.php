<?php
ob_start();

class dbgrid {

    private $link;

    private $table;

    private $caption;

    private $dbgrid_action;

    private $name_upload;

    private $old_name_upload;

    private $condition = " WHERE 1=1 ";

    private $join     = "";

    private $groupby  = "";

    private $search   = "";

    private $per_page = 20;

    private $page     = 1;

    private $fields   = "*";

    private $orderby  = "";

    private $sortdir  = "";

    private $salt               = "superSecret";

    private $user_directory     = "./uploads/";

    private $pagination_anchors = "";

    private $pagination_total   = "";

    private $pk                 = "";

    private $res_request          = array();

    private $structure            = array();

    private $force_import_field   = array();

    private $field_name           = array();

    private $field_hide           = array();

    private $field_search         = array();

    private $field_no_edit        = array();

    private $field_edit_condition = array();

    private $validation_callbacks = array();

    private $validation_type      = array();

    private $delete_callbacks     = array();

    private $insert_callbacks     = array();

    private $update_callbacks     = array();

    private $custom_toolbar       = array();

    private $notice               = array();

    private $error                = array();

    private $debug        = true;

    private $stopnow      = false;

    private $nocheckbox   = false;

    private $modify_pk    = false;

    private $allow_view   = true;

    private $allow_add    = true;

    private $allow_delete = true;

    private $allow_edit   = true;

    private $allow_export = true;

    private $allow_import = true;

    private $allow_search = false;

    /**
     * Constructor
     *
     * @param string $link
     * @return void
     */
    public function __construct($link) {
        $this->link    = $link;
        $this->get_request();
        return;
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct() {
        // nada
        return false;
    }

    private function get_request() {

        $get_post = array_merge($_GET,$_POST);
/*
        echo "<pre>";
        print_r($_GET);
        print_r($get_post);
        print_r($_POST);
        print_r($_REQUEST);
        echo "</pre>";
*/

        $cancel="";
        $ok="";
        foreach($get_post as $key=>$val) {
            if(substr($key,0,4)=="amp;") {
               $key=substr($key,4);
            }
            if($key=='dbgrid_page') {
                $this->page = intval($val);
            } elseif($key=='per_page') {
                $this->per_page = intval($val);
            } elseif($key=='dbgrid_sort') {
                $this->orderby=$val;
            } elseif($key=='dbgrid_sortdir') {
                $this->sortdir=$val;
            } elseif($key=='dbgrid_action'){
                $this->dbgrid_action=$val;
            } elseif($key=='dbgrid_notice'){
                $this->notice[]=$val;
            } elseif($key=='dbgrid_error'){
                $this->error[]=$val;
            } elseif($key=='dbgrid_search'){
                $this->search=$val;
            }
            if($key<>"button_ok" && $key <>"button_cancel" && 
               $key<>"dbgrid_notice" && $key<>"dbgrid_error") {
                $this->res_request[$key] = $val;
            }
            if($key=="button_cancel") {
                $cancel=$val;
            }
            if($key=="button_ok") {
                $ok=$val;
            }
        }
        if($cancel<>"" || $ok<>"" ) {
            if($cancel<>"") {
                $req = $this->set_request("dbgrid_action","list",true);
                $this->dbgrid_action="list";
            }
            // Si cancelamos algun form, hay que borar todos los campos de la tabla
            $table = ($cancel<>"")?$cancel:$this->table;
//            echo "mi tabla es $table<br>";
            $this->set_table($table);

            foreach ($this->structure as $field => $value){
                if(array_key_exists($field,$this->res_request)) {
                    if($ok<>"" && $field == $this->pk) {
                       // no borramos el id del request
                    } else {
                        unset($this->res_request[$field]);
                    }
                }
            }
        }
    }
    private function del_request($param) {
        unset($this->res_request[$param]);
        $ret = array();
        $return = "";
        foreach($this->res_request as $key=>$val) {
            $ret[]="$key=$val";
        }
        $return = implode("&amp;",$ret);
        if($return<>"") {
            $return ="?".$return;
        }
        return $return;
    }
    public function set_nocheckbox($val=true) {
        $this->nocheckbox=$val;
    }
    private function set_request($param,$value,$store=true) {
        $ret = array();
        if($store) {
            $this->res_request[$param]=$value;
        }
        
        $return = "";
        $estaba=0;
        foreach($this->res_request as $key=>$val) {
            if((!$store) && $param == $key) {
                $estaba=1;
                $ret[]="$param=$value";
            } else {
                $ret[]="$key=$val";
            }
        }
        if($estaba==0) {
            if(!array_key_exists($param,$this->res_request)) {
                $ret[]="$param=$value";
            }
        }
        $return = implode("&amp;",$ret);
        if($return<>"") {
            $return ="?".$return;
        }
        return $return;
    }

    //Permite habilitar la modificacion de PK
    private function change_pk($modify){
        $this->modify_pk=$modify;
    }

    public function set_pk($field){
        $this->pk=$field;
    }

    //Permite setear un directorio para administrar archivos
    public function set_user_directory($directory){
        $this->user_directory=$directory;
    }

    //Permite habilitar el boton agregar
    public function allow_add($allow_add){
        $this->allow_add=$allow_add;
    }

    //Permite habilitar el boton ver
    public function allow_view($allow_view){
        $this->allow_view=$allow_view;
    }


    //Permite habilitar el boton borrar
    public function allow_delete($allow_delete){
        $this->allow_delete=$allow_delete;
    }

    //Permite habilitar el boton editar 
    public function allow_edit($allow_edit){
        $this->allow_edit=$allow_edit;
    }

    //Permite habilitar el boton exportar 
    public function allow_export($allow_export){
        $this->allow_export=$allow_export;
    }

    //Permite habilitar el boton importar
    public function allow_import($allow_import){
        $this->allow_import=$allow_import;
    }

    //Buscar
    public function allow_search($allow_search){
       $this->allow_search=$allow_search;
    }

    public function salt($salt){
        $this->salt=$salt;
    }

    private function table_row($datos,$extraclass='',$key) {
        if($extraclass<>'') {
            $extraclass=" class='$extraclass'";
        }
        $ret = "<tr$extraclass>\n";

        $sumacolspan=0; 
        $cont=0;
        if ($this->allow_edit) {
            $cont++;
        }
        if ($this->allow_delete) {
            $cont++;
        }
        if($this->allow_view) {
            $cont++;
        }

        if($cont>0) {
            $sumacolspan=1;
            $ret.="<td class='tablecontrols'>";

            $req=$this->set_request($this->pk,$key,true);

            if($key<>-1) {
                if($this->allow_view) {
                    $req=$this->set_request('dbgrid_action','show',true);
                    $ret.="<a href='".SELF."$req'><i class='sprite sprite-eye' title='".trans('View Record')."'></i></a>";
                }

                if($this->allow_edit) {
                    $req=$this->set_request('dbgrid_action','edit',true);
                    $ret.="<a href='".SELF."$req'><i class='sprite sprite-edit' title='".trans('Edit Record')."'></i></a>";
                }

                if($this->allow_delete) {
                    $req=$this->set_request('dbgrid_action','delete',true);
                    $req=$this->set_request('dbgrid_pkhash',md5($this->salt.$key),false);
                    $ret.="<a href='".SELF."$req' title='".trans('Delete')."' onclick=\"confirmAction(this, '".trans("Are you sure?")."', performDelete); return false;\" > <i class='sprite sprite-delete'></a>";
                }
            }

        $this->del_request("dbgrid_action");
        $this->del_request($this->pk);
   
        $ret.="</td>";
        }
        $cont=0;

        if($this->nocheckbox != 1) {
           if($key<>-1) {
           $ret.="<td class='tcheck'><input type=checkbox id='dbgrid_checkbox_".$key."' /></td>";
           } else { $ret.="<td class='tcheck'></td>"; }
        }
        
        if(is_array($datos)) {
            $this->apply_display_filters($datos);
            foreach($datos as $campo=>$valor) {
                if (empty($this->structure[$campo]['display_filter'])) {
                    $valor = $this->clean_entities($valor);
                }
                $ret.="<td style='width:auto;'>$valor</td>";
            }
        } else {
            $datos = $this->clean_entities($datos);
            $ret.="<td>$datos</td>";
        }
        $ret .= "\n</tr>\n";
        return $ret;
    }

    
    private function table_head($datos,$orden) {
        $ret = "<tr>\n";
        $cont=0;
        if ($this->allow_edit) {
            $cont++;
        }
        if ($this->allow_delete) {
            $cont++;
        }
        if($this->allow_view) {
            $cont++;
        }
        $contclass=array();
        $contclass[3]="long";
        $contclass[2]="med";
        $contclass[1]="short";
        $contclass[0]="short";

        if($cont>0) {
            $ret.="<th class='tcontrol_$contclass[$cont]'>&nbsp;</th>";
        }

        if($this->nocheckbox != 1) {
            $ret.="<th class='tcheck'><input type=checkbox id='checkall' onclick='checkAll()' /></th>";
        }
        if(is_array($datos)) {
            $cont=0;
            foreach($datos as $valor) {
                //$valor = $this->clean_entities($valor);
                $req_uri = $this->set_request("dbgrid_sort",$orden[$cont],false);
                if($orden[$cont]==$this->orderby) {
                    $newdir = ($this->sortdir == "ASC")?"DESC":"ASC";
                    $arrow = ($this->sortdir == "ASC")?"sort_des":"sort_asc";
                    $req_uri = $this->set_request("dbgrid_sortdir",$newdir,false);
                    $add_arrow="<div class='$arrow'>&nbsp;</div>";
                } else {
//                    $req_uri = $this->del_request("dir");
                    $add_arrow="";
                }
                $ret.="<th style='text-align: left;'>$add_arrow <a href='".SELF."$req_uri'>$valor</a></th>";
                $cont++;
            }
        } else {
            //$datos = $this->clean_entities($datos);
            $ret.="<th><a href='".SELF."'>$datos</a></th>";
        }
        $ret .= "\n</tr>\n";
        return $ret;
    }


    public function set_table($table) {
        if($this->table) { return; }
        if(!$table) { return; }
        $this->table = $table;
        //Tipos de datos en mysql        
        $basetypes = 'real|double|float|decimal|numeric|tinyint|smallint|mediumint|int|bigint|date|time|timestamp|datetime|char|varchar|tinytext|text|mediumtext|longtext|enum|set|tinyblob|blob|mediumblob|longblob';
        $extra     = 'unsigned|zerofill|binary|ascii|unicode| ';
     
        $this->link->consulta("SET NAMES 'UTF8'");
        $res = $this->link->consulta("DESC $table");

        if(!$res) {
            $this->add_error($this->link->escape_string($this->link->error()));
            $this->stopnow = true;
            $this->print_grid();
            die();
        }

        while($row = $this->link->fetch_assoc($res)) {
            preg_match("#^($basetypes)(\([^)]+\))?($extra)*$#i", $row['Type'], $matches);
                // Tipo de dato?
            switch ($matches[1]) {
                case 'smalltext':
                case 'mediumtext':
                case 'text':
                case 'longtext':
                    $this->add_structure($row['Field'], 'textarea');
                    break;
                case 'enum':
                    $type   = substr($row['Type'], 6, -2);
                    $values = array_flip(preg_split("#','#", $type));
                    foreach ($values as $k => $v) {
                        $values[$k] = $k;
                    }
                    $this->add_structure($row['Field'], 'select', $values,$row['Default']);
                    break;
                case 'date':
                    $this->add_structure($row['Field'], 'date', null, date('Y-m-d'));
                    break;
                case 'time':
                    $this->add_structure($row['Field'], 'time', null, date('H:i:s'));
                    break;
                case 'datetime':
                    $this->add_structure($row['Field'], 'datetime', null, date('Y-m-d H:i:s'));
                    break;
                default:
                    $this->add_structure($row['Field'], 'text',null,$row['Default']);
           }
            //$this->structure[$row['Field']]=$row;
            if ($row['Key'] == 'PRI'){
                $this->pk = $row['Field']; 
            }
        }
    }

    public function force_import_field($field,$value){
        $this->force_import_field[$field] = $value;
    }

    public function add_structure($name, $inputType, $values = null, $default = null, $instructions = null, $foreigntable = null, $foreignkey = null){
        $this->structure[$name] = array ('display'        => $name,
                                         'input'          => $inputType,
                                         'values'         => $values,
                                         'display_filter' => array(),
                                         'edit_filter'    => array(),
                                         'instructions'   => $instructions,
                                         'foreigntable'   => $foreigntable,
                                         'foreignkey'     => $foreignkey,
                                         'default'        => $default);
    }

    public function set_caption($caption) {
        $this->caption = $caption;
    }

    public function set_orderby($orderby) {
        if(!$this->orderby) {
            $this->orderby = $orderby;
        }
    }

    public function set_orderdirection($orderby) {
        if(!$this->sortdir) {
            $this->sortdir = $orderby;
        } 
    }

    public function set_join($join) {
        $this->join = $join;
    }

    public function set_groupby($groupby) {
        $this->groupby = $groupby;
    }

    public function set_condition($condition) {
        $this->condition = "WHERE ".$condition." ";
    }

    public function set_search($search) {
        $this->search = $search;
    }

    public function set_input_style($campo,$style) {
        if(array_key_exists($campo,$this->structure)) {
            $this->structure[$campo]['style']=$style;
        }
    }

    public function set_input_parent_style($campo,$style) {
        if(array_key_exists($campo,$this->structure)) {
            $this->structure[$campo]['parentstyle']=$style;
        }
    }

    public function set_field_explode($campo) {
        if(array_key_exists($campo,$this->structure)) {
            $this->structure[$campo]['explode']=1;
        } else {
           print_r($this->structure);
        }
    }

    public function set_input_type($campo,$tipo,$valores='') {
        if(array_key_exists($campo,$this->structure)) {
            $this->structure[$campo]['input']=$tipo;
            if(isset($valores)) {
                $this->structure[$campo]['values']=$valores;
            } 
        }
    }

    public function set_fields($fields) {
        $this->fields = $fields;
    }

    public function set_per_page($perpage) {
        $this->per_page = $perpage;
    }

    private function clean_entities($param) {
        return is_array($param) ? array_map('clean_entities', $param) : htmlspecialchars($param, ENT_QUOTES);
    }

    private function construct_count_query() {
        $query = "SELECT count(*) FROM ".$this->table." ";
        if($this->join <> "") {
            $query .= $this->join;
        }
        if($this->condition <> "") {
            $query .= " ".$this->condition;
        }
        if($this->search <> "") {
            $query_parts=array();

            foreach ($this->field_search as $key=>$val ){
                $query_parts[]= "$key LIKE '%%".$this->search."%%'";
            }
            if(count($query_parts) > 0) {
                $searchquery=implode(" OR ",$query_parts);
                $query .= " AND ( ".$searchquery.") ";
            }

        }
        if($this->groupby <> "") {
           $query .= " ".$this->groupby;
        }
        return $query;
    }

    private function construct_query($limit=true) {
        $vars   = Array();
        $return = Array();

        if($this->stopnow) {
            $return[] = "";
            $return[] = $vars;
            return $return;
        } 

        $query = "SELECT ".$this->fields." FROM ".$this->table." ";
//        $vars[]=$this->fields;
//        $vars[]=$this->table;

        if($this->join <> "") {
           $query .= " ".$this->join." ";
        }

        if($this->condition <> "") {
           $query .= $this->condition;
        }

        if($this->search <> "") {
            $query_parts=array();
            foreach ($this->field_search as $key=>$val ){
                $query_parts[]= "$key LIKE '%%".$this->search."%%'";
            }
            if(count($query_parts) > 0) {
                $searchquery=implode(" OR ",$query_parts);
                $query .= " AND (".$searchquery.") ";
            }
        }

        if($this->groupby <> "") {
           $query .= $this->groupby;
        }

        if(array_key_exists('dbgrid_sort',$this->res_request)) {
               $query .= " ORDER BY %s %s ";
               $vars[]=$this->orderby;
               $vars[]=$this->sortdir;
        } else {
            if($this->orderby <> "") {
                $query .= " ORDER BY %s %s ";
                $vars[]=$this->orderby;
                $vars[]=$this->sortdir;
            }
        }
        if($limit) {
            $start_record = ($this->page * $this->per_page) - $this->per_page ;
            $query .= " LIMIT %s,%s ";
            $vars[]=$start_record;
            $vars[]=$this->per_page;
        }

        $return[] = $query;
        $return[] = $vars;
        return $return;
    }

    private function set_pagination() {
        $query   = $this->construct_count_query();
        $rst     = $this->link->consulta($query);
        if($this->groupby == "") {
            list($numrows) = $this->link->fetch_row($rst);
        } else {
            $numrows = $this->link->num_rows();
        }
        $anc     = '';

        $next  = $this->page+1;
        $var   = ((intval($numrows/$this->per_page))-1)*$this->per_page;
        $last  = ceil($numrows/$this->per_page);

        $previous = $this->page-1;

        $anc = "<div class='pagination'>"; 

        if($previous <= 0){
            $anc .= "<span class='disabled' title='".trans('First')."'>&laquo;</span><span class='disabled' title='".trans('Previous')."'>&lsaquo;</span>";
        }else{
            $req_uri = $this->set_request('dbgrid_page',1,false);
            $anc .= "<a href='".SELF."$req_uri' title='".trans('First')."'>&laquo;</a>\n";
            $req_uri = $this->set_request('dbgrid_page',$previous,false);
            $anc .= "<a href='".SELF."$req_uri' title='".trans('Previous')."'>&lsaquo;</a>";
        }
        

        $norepeat = 4; // numero de paginas a mostrar a izq y der
        $anch     = "";
        $j = 1;
        for($i=$this->page; $i>1; $i--){
            $page = $i-1;
            $req_uri = $this->set_request('dbgrid_page',$page,false);
            $anch = "<a href='".SELF."$req_uri'>$page</a>".$anch;
            if($j == $norepeat) break;
            $j++;
        }
        $anc .= $anch;

        $anc .= "<span class='current'>".$this->page."</span>\n";
        $j = 1;
        for($i=$this->page; $i<$last; $i++){
            $page = $i+1;
            $req_uri = $this->set_request('dbgrid_page',$page,false);
            $anc .= "<a href='".SELF."$req_uri'>$page</a>\n";
            if($j==$norepeat) break;
            $j++;
        }
        
        if($this->page >= $last){
            $anc .= "<span class='disabled' title='".trans('Next')."'>&rsaquo;</span><span class='disabled' title='".trans('Last')."'>&raquo;</span>\n";
        }else{
            $req_uri = $this->set_request('dbgrid_page',$next,false);
            $anc .= "<a href='".SELF."$req_uri' title='".trans('Next')."'>&rsaquo;</a>";

            $req_uri = $this->set_request('dbgrid_page',$last,false);
            $anc .= "<a href='".SELF."$req_uri' title='".trans('Last')."'>&raquo;</a>";
        }
        $anc.="</div>\n";
        $this->pagination_anchors = $anc;
        
        $this->pagination_total = "<span style='text-align:center;' class='gray' >".trans('Page')." : $this->page <i> ".trans('of')."  </i> $last . ".trans('Total records found').": $numrows</span>";
    }

    public function hide_field($field) {
        if(is_array($field)) {
           foreach($field as $campo) {
               $this->field_hide[$campo]=1;
           }
        } else {
            $this->field_hide[$field]=1;
        }
    }

    public function set_default_values($fields,$values) {
        $cont=0;
        if(is_array($fields)) {
           foreach($fields as $campo) {
               if(array_key_exists($campo,$this->structure)) {
                   $this->stucture[$campo]['default']=$values[$cont];
               }
               $cont++;
           }
        } else {
            if(array_key_exists($fields,$this->structure)) {
                $this->structure[$fields]['default']=$values;
            } 
        }
    }

    public function no_edit_field($field){
        if(is_array($field)) {
           foreach($field as $campo) {
               $this->field_no_edit[$campo]=1;
           }
        } else {
            $this->field_no_edit[$field]=1;
        }
    }

    public function edit_field_condition($field,$conditionfield,$cond,$value) {
            $this->field_edit_condition[$field]="$conditionfield|$cond|$value";
    }

    public function set_display_name($field,$newname) {
        if(is_array($field)) {
           $cont=0;
           foreach($field as $campo) {
               $this->field_name[$campo]=$newname[$cont];
               $cont++;
           }
        } else {
            $this->field_name[$field]=$newname;
        }
    }

       //Buscar
    public function set_search_fields($fields) {
        if(is_array($fields)) {
            foreach($fields as $campo) {
                $this->field_search[$campo]=1;
            }
        } else {
            $this->field_search[$fields]=1;
        }
     }

    public function add_error($error) {
        $this->error[]=$error;
    }
    public function add_notice($error) {
        $this->notice[]=$error;
    }

    public function add_custom_toolbar($texto) {
         $this->custom_toolbar[] = $texto;
    }
   /**
    * Adds an delete callback. Gets called when a row is deleted. All
    * row data is passed as an array to the callback function.
    *
    * @param callback $callback The callback to be used
    */
    public function add_delete_callback($callback) {
       if (is_callable($callback)) {
           $this->delete_callbacks[] = $callback;
       } else {
           $this->error[] = "Fallo al agregar delete callback - callback no valido";
       }
    }

    public function add_insert_callback($callback) {
       if (is_callable($callback)) {
           $this->insert_callbacks[] = $callback;
       } else {
           $this->error[] = "Fallo al agregar insert callback - callback no valido";
       }
    }
 
    public function add_update_callback($callback) {
       if (is_callable($callback)) {
           $this->update_callbacks[] = $callback;
       } else {
           $this->error[] = "Fallo al agregar update callback - callback no valido";
       }
    }

    public function add_validation_callback($field, $callback) {
        if (!is_callable($callback)) {
           $this->error[] = "Fallo al agregar validation callback - callback no valido";
        } else {
            if (!empty($this->structure[$field]) AND empty($this->field_no_edit[$field])) {
                $this->validation_callbacks[$field][] = $callback;
            }
        }
    }

    public function add_validation_type($field, $type) {
        // Tipos validos:
        // required
        // alfanumeric
        // text
        // numeric
        // email
        // url 
        if (!empty($this->structure[$field]) AND empty($this->field_no_edit[$field])) {
            $this->validation_type[$field][] = $type;
        }
    }

    public function add_display_filter($field, $callback) {
        if (is_callable($callback) AND isset($this->structure[$field])) {
            $this->structure[$field]['display_filter'][] = $callback;

        } else if (is_callable($callback)) {
            $this->error[] = "Unknown field: $field";

        } else {
            $this->error[] = "Fallo al agregar display filter - callback no valido";
        }
    }

    /**
    * Applys display filters to a single row of data. Does htmlspecialchars() first.
    *
	* @param array &$results Data from table
	 */
    private function apply_display_filters(&$row) {
        foreach ($row as $field => $value) {
            if (!empty($this->structure[$field]['display_filter'])) {
                foreach ($this->structure[$field]['display_filter'] as $f) {
                    $value = call_user_func($f, $value);
                }

                $row[$field] = $value;
            }
        }
    }

    public function add_edit_filter($field, $callback) {
        if (is_callable($callback) AND isset($this->structure[$field])) {
            $this->structure[$field]['edit_filter'][] = $callback;

        } else if (is_callable($callback)) {
            $this->error[] = "Unknown field: $field";

        } else {
            $this->error[] = "Fallo al agregar edit filter - callback no valido";
        }
    }

    private function get_edit_filtered($field,$value) {
        if (!empty($this->structure[$field]['edit_filter'])) {
            foreach ($this->structure[$field]['edit_filter'] as $f) {
                $value = call_user_func($f, $value);
            }
        } 
        return $value;
    }

    private function check_validation_type($field,$value) {
        foreach ($this->validation_type[$field] as $type) {
            $fieldname = isset($this->field_name[$field])?$this->field_name[$field]:$field;
            switch($type) {
                case 'required':
                    if(strlen($value)==0) {
                        $this->add_error("El campo $fieldname es obligatorio");
                    }
                    break;
                case 'alfanumeric':
                    if(!$this->isAlfaNumeric($value)) {
                        $this->add_error("El campo $fieldname solo puede contener letras y numeros");
                    }
                    break;
                case 'text':
                    if(!$this->isAlfa($value)) {
                        $this->add_error("El campo $fieldname solo puede contener letras");
                    }
                    break;
                case 'numeric':
                    if(!is_numeric($value)) {
                        $this->add_error("El campo $fieldname debe ser numerico");
                    }
                    break;
                case 'email':
                    if(!$this->isEmail($value)) {
                        $this->add_error("El campo $fieldname no es un email valido");
                    }
                    break;
                case 'url':
                    if(!$this->isURL($value)) {
                        $this->add_error("El campo $fieldname no es un url valido");
                    }
                    break;
            }
        }
    }

    public function show_grid() {
        switch($this->dbgrid_action) {
            case 'csv':
                $this->export_csv();
                break;
            case 'import':
                $this->import_csv();
                break;
            case 'delete_marked':
                $this->delete_marked();
                break;
            case 'list':
                $this->print_grid();
                break;
            case 'edit':
                if(array_key_exists($this->pk,$this->res_request)) {
                    $this->edit_form($this->res_request[$this->pk]);
                }
                break;
            case 'editSave':
                if(array_key_exists($this->pk,$this->res_request)) {
                    $this->update_row($this->res_request[$this->pk]);
                }                
                break;
            case 'show':
                if(array_key_exists($this->pk,$this->res_request)) {
                    $this->show_row($this->res_request[$this->pk]);
                } else {
                    print_r($_REQUEST);
                }
                break;
            case 'delete':
                if(array_key_exists($this->pk,$this->res_request)) {
                    $this->delete_rows(array($this->res_request[$this->pk]));
                }
                break;
            case 'add':
                $this->add_form();
                break;
            case 'addSave':
                $this->insert_row();
                break;
            case 'search':
                $this->search_rows();
                break;
            default:
                $this->print_grid();
        }        
    }

    private function print_toolbar(){

        $sep=0;
        $buttons= "
        <table class='toolbarTable' style='width: 100%;'>
            <thead>
                <tr>
                    <td class='tb'>";

        foreach($this->custom_toolbar as $texto) {
           $buttons.=$texto;
           $sep=1;
        }

        if ($this->allow_add) {
            $req=$this->set_request('dbgrid_action','add',true);
            $buttons.= "
                    <div class='fbutton'>
                        <span class='btnText' onclick='javascript:location=\"".SELF.$req."\"'><i class='sprite sprite-add'></i>".trans('Add')."</span>
                    </div>\n";
            $sep=1;
        }

        if ($this->allow_delete) {
            $req=$this->set_request('dbgrid_action','delete_marked',false);

            if($sep==1) { $buttons.=" <div class='btnseparator'></div>"; }

            $buttons.="
                    <div class='fbutton'>
                        <span class='btnText' onclick='prepare_marked(this)'><i class='sprite sprite-delete'></i>".trans('Delete Marked')."</span>
                    </div>
            ";
            $sep=1;

        }

        if ($this->allow_export) {
            $req=$this->set_request('dbgrid_action','csv',false);
            if($sep==1) { $buttons.=" <div class='btnseparator'></div>"; }

            $buttons.="
                    <div class='fbutton'>
                        <span class='btnText' onclick='javascript:location=\"".SELF.$req."\"'><i class='sprite sprite-export'></i>".trans('Export')."</span>
                    </div>
            ";
            $sep=1;
        }

        if ($this->allow_import) {
            $req=$this->set_request('dbgrid_import','',false);
            if($sep==1) { $buttons.=" <div class='btnseparator'></div>"; }
            $params = explode("&amp;",$req);
            $buttons.="
                    <div class='fbutton'>
                        <span class='btnText' onclick='javascript:ImportFile();'><i class='sprite sprite-import'></i>".trans('Import')."</span>
                    </div>
                    <div id='importfile' style='display:none; float:left;' >
                         <form method='post' id='dbgrid_importmyfile' action='".SELF."' enctype='multipart/form-data'>
                         <input type='hidden' name='dbgrid_action' value='import' />
                         <input type='file' name='dbgrid_import' />
                         </form>
                    </div> 
            ";
            $sep=1;
        }


        if ($this->allow_search) {
            $req=$this->set_request('dbgrid_action','search',true);
            $req=$this->set_request('dbgrid_page',1,false);
            if($sep==1) { $buttons.=" <div class='btnseparator'></div>"; }
            $params = explode("&",$req);
            $buttons.="
                     <div>
                         <form method='post' action='".SELF.$req."' id='dbgrid_sform'>
                         <input type='text' name='dbgrid_search' class='dbgrid_search' value='".$this->search."'>";
                               foreach($params as $v) {
                                   list($key,$val)=preg_split("/=/",$v);
                                   if ($key=='dbgrid_sortdir' || $key=='dbgrid_sort'){
                                      $buttons.="<input type=hidden name='$key' value='$val'>\n";
                                   }
                               }
            $buttons.="<div class='fbutton'>
                         <span class='btnText' onclick='javascript:document.forms.dbgrid_sform.submit();'><i class='sprite sprite-search'></i>".trans('Search')."</span>
                       </div>
                          </form>
                       </div>
            ";
            $sep=1;
        }
       

        $buttons.="
                        </td>
                    </tr>
                </thead>
                <tbody style='display:none;'><tr><td></td></tr></tbody>
            </table>";
            $this->del_request("dbgrid_action");
            echo $buttons;
    }

    private function import_csv(){

        $valid_field = Array();

        foreach ($this->structure as $field => $value){
            if(trim($field)<>"id") {
               $valid_field[]=trim($field);
            }
        }

        $arrFile = $_FILES['dbgrid_import'];
        $file = $arrFile['tmp_name'];

        if ($arrFile['size']>0 && !empty($file)) {
            if (is_uploaded_file($file)) {
                if (copy ($file, $this->user_directory."importCSV-".$arrFile['name'])) {
                    $this->name_upload="importCSV-".$arrFile['name'];
                }else{
                    $this->add_error(trans('Could not copy uploaded file'));
                }
            }else{
                $this->add_error(trans('Could not upload file'));
            }
        }else{
            $this->add_error(trans('Could not upload file'));
        }
     
        if($this->name_upload == "") {
            $this->add_error(trans('Empty file?'));
        } else {

        $mifilename = $this->user_directory.$this->name_upload;
        $importar = file($mifilename);
        unlink($this->user_directory.$this->name_upload);

        $cuantos = count($importar);
        if($cuantos>0) {
            $head = array_shift($importar);
            $columns=explode(",",$head);
            $parasql = Array();
            $forcedvalues = Array();
            $valid_field_number = Array();
            $count=0;

            foreach($columns as $campo) {
                if(in_array(trim($campo),$valid_field)) {
                    if(!array_key_exists(trim($campo),$this->force_import_field)) {
                        $valid_field_number[] = $count;
                        $parasql[]=trim($campo);
                    }
                } else {
                    $this->add_error(trans('Ignoring field ').$campo);
                }
                $count++;
            }

            if(count($valid_field_number)==0) {
                $this->add_error(trans('No valid fields to import!'));
                $this->print_grid();
                die();
            }

            foreach ($this->force_import_field as $key=>$val) {
                $parasql[]=$key;
                //$forcedvalues[]=mysql_real_escape_string($val);
                $forcedvalues[] = $this->link->escape_string($val);
            }

            $columnsql=implode(",",$parasql);
            $forceval=implode("','",$forcedvalues);

            foreach($importar as $linea) {
                $linea = trim($linea);
                if($linea=="") { $cuantos--; continue; };
                $misdatos = Array();
                $columns=explode(",",$linea);
                foreach($valid_field_number as $nro) {
                    //$misdatos[]=mysql_real_escape_string(trim($columns[$nro]));
                    $misdatos[]= $this->link->escape_string(trim($columns[$nro]));
                }

                $valuesql=implode("','",$misdatos);
                $query = "INSERT INTO $this->table ($columnsql) VALUES ('$valuesql','$forceval')";
                $this->link->consulta($query);
            }
            $cuantos--;
            $this->add_notice($cuantos." ".trans('Records imported'));
        }
        }
        $this->print_grid();
    }

    private function delete_marked(){
        $myids = $this->res_request["dbgrid_delete_id"];
        $this->del_request("dbgrid_action");
        $this->del_request("dbgrid_delete_id");
        if($myids <> "") {
            $ids_a_borrar = explode(",",$myids);
            $this->delete_rows($ids_a_borrar,'marked');
        } else {
            $this->print_grid();
        }
    }

    private function export_csv(){
        list ($query,$vars) = $this->construct_query(false);
        $res = $this->link->consulta($query,$vars);
        if($this->link->num_rows()==0) {
            $this->add_error(trans('There are no records to export'));
            $this->print_grid();
            return;
        }
        @ob_end_clean();
        //ob_start();
        //header("Content-Type: application/csv-tab-delimited-table");
        //header("Content-disposition: filename=data.csv");
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=data.csv");
        $cont=1;
        while ($row = $this->link->fetch_assoc($res)) {
            if($cont==1) {
                $columnas = Array();
                foreach($row as $columna=>$valor) {
                    $columnas[] = $columna;
                }
                $header = implode(",",$columnas);
                echo "$header\n";
            } 
            $milinea = Array();
            foreach($row as $columna=>$valor) {
                $milinea[] = $valor;
            }
            $linea = implode(",",$milinea);
            echo "$linea\n";
            $cont++;
        }
        //ob_end_flush();
        die();
    }

    private function print_grid(){
        // construye la consulta en base a modificadores, etc.
        list ($query,$vars) = $this->construct_query();

        $this->print_javascript();

        if($this->link->error()) {
            $this->print_errors();
            return;
        }

        // realiza la consulta
        $gridresult = $this->link->consulta($query,$vars);

        // comienza a imprimir la tabla
        $this->print_errors();


        //echo "<form name='edit'>\n";
        echo "<div class='stable' style='margin:auto;'>\n";
        ///echo "<table class='xstable'>\n";

        // CAPTION
        if($this->caption <> "") {
            echo "<caption>".$this->caption."</caption>\n";
        }

        // HEADING
        // recupera los nombres de los campos y los guarda en un array
        $headings = $this->link->field_name_array();

        $headings_final  = array();
        $orden           = array();

        $cont=0;
        foreach($headings as $h) {
            $cont++;
            if(array_key_exists($h,$this->field_hide)) {
                // Salteamos los campos hide
                continue;
            }
            $orden[] = $cont;
            if(array_key_exists($h,$this->field_name)) {
                $headings_final[] = $this->field_name[$h];
            } else {
                $headings_final[] = $h;
            }
        }

        $sumacolspan=0; 
        $cont=0;
        if ($this->allow_edit) {
            $cont++;
        }
        if ($this->allow_delete) {
            $cont++;
        }
        if($this->allow_view) {
            $cont++;
        }

        if($cont>0) {
            $sumacolspan=1;
        }
        $cont=0;
 
        ///echo "<thead>\n";
        ///echo "<tr><td style='padding:0px; border:0;'>";
        $this->print_toolbar();
        ///echo "</td></tr>";
        ///echo "</thead>\n";
        ///echo "</table>";

        echo "<table class='stable'>\n";
        echo "<thead>";

        echo $this->table_head($headings_final,$orden);
        echo "</thead>";
        //echo "<tbody><form name='edit'>\n";
        echo "<tbody>\n";

        $colspan = count($headings_final)+1+$sumacolspan;

        if($this->nocheckbox == 1) {
            $colspan--;
        }

        if ($this->link->num_rows() > 0) {
            $cont=0;
            while ($r = $this->link->fetch_assoc($gridresult)) {
                // oculta los campos hide
                if(is_callable("array_diff_key")) {
                    $j = array_diff_key($r,$this->field_hide);
                } else {
                    $j = PHP4_array_diff_key($r,$this->field_hide);
                }
                $class = $cont%2?'':'odd';
                echo $this->table_row($j,$class,$r[$this->pk]);
                $cont++;
            }
            if($cont<$this->per_page) {
                $faltan = $this->per_page - $cont;
                $newj = array();
                foreach($j as $fieldname=>$fieldvalue) {
                    $newj[$fieldname]='';
                }
                for($a=0;$a<$faltan;$a++) {
                    $class = $cont%2?'':'odd';
                    echo $this->table_row($newj,$class,-1);
                    $cont++;
                }
            }
        } else {
            echo "<tr><td colspan='$colspan'><div style='text-align:center; font-weight: bold;'>".trans('No records found')."</div></td></tr>";
        }
        //echo "</form></tbody>";
        echo "</tbody>";
        // FOOTER
        echo "<tfoot><tr><th colspan='".$colspan."'>";
        $this->set_pagination();

        echo $this->pagination_anchors;
        echo $this->pagination_total;

        echo "</th></tr></tfoot>";

        // END
        echo "</table>\n";
        echo "</div>\n";
        //echo "</form>";


        return false;    
    
    }

    private function add_form(){

        if (!$this->allow_add) {
            return;
        }

        $req = substr($this->set_request('dbgrid_action','addSave',false),1);
        $params = explode("&amp;",$req);

        $this->print_errors();

        echo "<form method='post' dbgrid_action='".SELF."' enctype='multipart/form-data'>\n";
        // comienza a imprimir la tabla
        foreach($params as $v) {
            list($key,$val)=preg_split("/=/",$v);
            echo "<input type=hidden name='$key' value='$val'>\n";
        }
        echo "<fieldset>\n";
        echo "<legend>".trans('Add Record')."</legend>\n";
        echo "\n";
        foreach ($this->structure as $field => $value){
            if(isset($this->field_no_edit[$field]) && $field!=$this->pk) {
               continue;
            }
            if ($field!=$this->pk)    {
                $this->create_body_form($field,$this->structure[$field]['default'],"add");    
            }

        }
        $req = $this->set_request('dbgrid_action','list',true);
        $req = $this->del_request($this->pk);
        echo "
            <div style='margin-top:5px; clear:both;'>
            <input type='submit' class='alertify-button alertify-button-ok' name='button_ok' value='".trans('Save')."'>
            <input type='submit' class='alertify-button alertify-button-cancel' name='button_cancel' value='".trans('Cancel')."' onClick='javascript:location=\"".SELF."$req\"; return false;'></div>";
        echo "</fieldset></form>";

    }

    private function insert_row() {


        if (!$this->allow_add) {
            return;
        }

        $status      = "OK";
        $random      = "DBGRID-".rand();
        $callbacks   = !empty($this->insert_callbacks);
        $add_fields  = array();
        $name_temp   = array();
        $query_parts = array();
        $queryval    = array();


        foreach ($this->structure as $field => $value){
                if (array_key_exists($field,$_POST)){

                    // Procesa validacion
                    if (!empty($this->validation_type[$field])) {
                        $this->check_validation_type($field,$_POST[$field]);
                    }

                    // Procesa callback de validacion
                    if (!empty($this->validation_callbacks[$field])) {
                        foreach ($this->validation_callbacks[$field] as $c) {
                            $_POST[$field] = call_user_func($c, $this, $_POST[$field], $_POST);
                        }
                    }

                    if ($field!=$this->pk){
                        $add_fields[$field]=$_POST[$field];
                    }
                }
                if (array_key_exists($field,$_FILES)){
                    if ($field!=$this->pk) {
                        $add_fields[$field]=$_FILES[$field];
                    }
                }
        }


//        echo "<pre>";
//        print_r($add_fields);
//        echo "</pre>";

        if (sizeof($add_fields)>0){

            $query = "INSERT INTO $this->table SET ";

            foreach ($add_fields as $field => $value){
                if ($this->structure[$field]['input']=="img"){
                    if($_FILES[$field]['name']<>"") {
                        $name_temp[]=array('field'=>$field,'temp_name'=>$random."-".$value['name'],'name_file'=>$value['name']);
                        $this->upload_img($random,$_FILES[$field],$field);
                    }
                    $value="";
                    if(count($this->error)>0) { break; }
                }
                else if($this->structure[$field]['input']=='bitmask') {
                     $totbit=0;
                     foreach($value as $indv) {
                        $totbit+=$indv;
                     }
                     $value=$totbit;
                } 
                else if($this->structure[$field]['input']=='multiselect') {
                     $finvalue = implode(",",$value);
                     $value    = $finvalue;
                }

                $query_parts[]= "`%s`='%s'";
                $queryval[]=$field;
                $queryval[]=$value;
                
                $this->del_request($field);
            }
        }

        $query.=implode(",",$query_parts);

        if(count($this->error)>0) {
            $this->add_form();
            return;
        }
        $res  = $this->link->consulta($query,$queryval);

        if($res) {
            $this->set_request('dbgrid_notice',trans('Record inserted'),true);
            $insert_id = $this->link->insert_id();

            // Manipulamos nombre de archivo en base
            if (sizeof($name_temp)>0){
                foreach ($name_temp as $key => $value){
                    //print "nametemp $key = $value<br>";
                    if (rename($this->user_directory.$value['temp_name'], $this->user_directory.$insert_id."-".$value['name_file'])){
                        $this->set_request('dbgrid_notice',trans('File was uploaded successfully'),true);
                        $temp_field=$value['field'];
                        $temp_name_file=$insert_id."-".$value['name_file'];
                        $query="UPDATE $this->table SET $temp_field = '$temp_name_file' WHERE $this->pk = $insert_id";
                        $this->link->consulta($query);
                    }
                    else{
                        $this->set_request('dbgrid_error',trans('Problems with the file upload. Check permissions on the upload directory.'),true);
                        echo "fallo rename ".$this->user_directory.$value['temp_name']."<br>";
                    }
                }
            }
            if($callbacks) {
                $add_fields[$this->pk]=$insert_id;
                foreach ($this->insert_callbacks as $c) {
                    call_user_func($c, $add_fields);
               }
           }
        } else {
           $this->set_request('dbgrid_error',trans('Error inserting record:<br>').$this->link->error(),true);
           // borrar archivo subido
        }

        $this->del_request("button_ok");
        $req=$this->set_request('dbgrid_action','list',true);
        //$this->print_grid();

        header("Location: ".SELF."$req"); 
    }

    private function print_javascript() {

        $req=$this->set_request('dbgrid_action','delete_marked',true);
        $req=$this->set_request('dbgrid_pkhash',md5($this->salt.'marked'),false);

         echo "

<script>

function routeClick(element) {
    $(''+element).click();
}

function prepare_marked(me) {
    var inputs = document.getElementsByTagName('input');
    var totnumero='';
    for (var i=0; i < inputs.length; i++) {
        if(inputs[i].id.indexOf('dbgrid_checkbox')==0) {
            if(inputs[i].checked) {
                var minumero = inputs[i].id.substring(16);
                totnumero+=minumero+',';
            }
        }
    }

    if(totnumero.length>0) {
       alertify.confirm('".trans("Are you sure?")."', function (e) {
           if (e) {
               // user clicked ok
               totnumero = totnumero.substring(0,totnumero.length-1);
               location = '".SELF.$req."&amp;dbgrid_delete_id='+totnumero;
           } else {
               // user clicked cancel
               return;
           }
       });
    }
}

function markCheckBox(n) {
    if(n.nodeType == 1) {
       if(n.id.indexOf('dbgrid_checkbox')==0) {
            if(n.id.indexOf('dbgrid_checkbox')==0) {
                n.checked = !n.checked;
            }
       }
    }
    for(var m = n.firstChild; m != null; m = m.nextSibling) {
        markCheckBox(m);
    }
}


function checkAll() {
    field = document.body;
    markCheckBox(field);
}

function ImportFile(me) {
    var estavisible = isvisible('importfile');
    if(estavisible=='none') {
        showdiv('importfile');
    } else {
        hidediv('importfile');
        document.forms.dbgrid_importmyfile.submit();
    }
}

function isvisible(id) {
    if (document.getElementById) { 
       return document.getElementById(id).style.display;
    } else {
        if (document.layers) { 
            return document.id.display;
        } else {
            return document.all.id.style.display;
        }
    }
}

   
function hidediv(id) {
	//safe function to hide an element with a specified id
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'none';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'none';
		}
		else { // IE 4
			document.all.id.style.display = 'none';
		}
	}
}

function showdiv(id) {
	//safe function to show an element with a specified id
		  
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'block';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'block';
		}
		else { // IE 4
			document.all.id.style.display = 'block';
		}
	}
}

function performDelete ( a_element ) {
    var dlink = document.getElementById('deletelink');
    location=a_element.href;
}

function confirmAction ( a_element, message, action ) {
    alertify.confirm(message, function(e) {
        if (e) {
            // a_element is the <a> tag that was clicked
            if (action) {
                action(a_element);
                return true;
            }
        }
    });
}

</script>
";
    
    }

    private function print_errors() {
        global $jsnotifications;

        if($jsnotifications==1) {

            echo "<script type='text/javascript'>\n";

            if (count($this->error)>0) {
                echo "var error_type='message_error';\n";
                echo "var error_text='";
                foreach($this->error as $error) {
                     echo "<p>$error</p>";
                }
                echo "';\n";
            } else 
                if (count($this->notice)>0) {
                    echo "var error_type='message_success';\n";
                    echo "var error_text='";
                    foreach($this->notice as $notice) {
                        echo "<p>$notice</p>";
                    }
                    echo "';\n";

            }
            echo "</script>\n";

        } else {
            if(count($this->notice)>0) {
                //echo "<div class='successBox' id='noticebox'>\n";
                //echo "  <div class='successIcon'></div>\n";
                //echo "  <div class='msgBoxContent'>\n";
                //echo "  <p>".trans('Success!')."</p> \n";
                //foreach($this->notice as $notice) {
                //    echo "<p>$notice</p>";
                //}
                //echo "  </div>\n";
                //echo "</div>\n";
                //echo "<script type='text/javascript'>\n";
                //echo "window.setTimeout(\"hidediv('noticebox')\",3000);\n";
                //echo "</script>\n";
                echo "<script type='text/javascript'>\n";
                foreach($this->notice as $notice) {
                    echo "alertify.success('$notice');\n";
                }
                echo "</script>\n";
            }

            if(count($this->error)>0) {
                //echo "<div class='errorBox' id='errorbox'>\n";
                //echo "  <div class='errorIcon'></div>\n";
                //echo "  <div class='msgBoxContent'>\n";
                //echo "  <p>".trans('An error has occurred')."</p> \n";
                //foreach($this->error as $error) {
                //    echo "  <p>$error</p>\n";
                //}
                //echo "  </div>\n";
                //echo "</div>\n";
                //echo "<script type='text/javascript'>\n";
                //echo "window.setTimeout(\"hidediv('errorbox')\",3000);\n";
                //echo "</script>\n";
                echo "<script type='text/javascript'>\n";
                foreach($this->notice as $notice) {
                    echo "alertify.error('$notice');\n";
                }
                echo "</script>\n";
            }
        }
    }
    
    private function edit_form($id) {
        if (!$this->allow_edit) {
            return;
        }
        $query = "SELECT $this->fields FROM $this->table $this->join WHERE $this->table.$this->pk = '$id' $this->groupby";

        // realiza la consulta
        $res = $this->link->consulta($query);
        $this->set_request('dbgrid_action','editSave',true);
        $req = substr($this->set_request($this->pk,$id,false),1);
        
        $params = explode("&amp;",$req);

        $this->print_errors();

        echo "<form method='post' action='".SELF."' enctype='multipart/form-data'>\n";
        // comienza a imprimir la tabla
        foreach($params as $v) {
            list($key,$val)=preg_split("/=/",$v);
            echo "<input type=hidden name='$key' value='$val'>\n";
            if($this->pk == $key) {
                echo "<input type=hidden name='dbgrid_pkhash' value='".md5($this->salt.$val)."'>";
            }
        }
        echo "<fieldset>\n";
        echo "<legend>".trans('Edit Record')."</legend>\n";

        if ($this->link->num_rows() > 0) {
            $cont=0;
            $rowresult = $this->link->fetch_assoc($res);
            foreach ($rowresult as $field=>$value) {
                $valor_de[$field]=$value;
            }
            foreach ($rowresult as $field=>$value) {
                if (isset($this->field_edit_condition[$field])){
                if ($this->field_edit_condition[$field]){
                     list($condfield,$condcond,$condvalue) = preg_split("/\|/",$this->field_edit_condition[$field]);
                     if($condcond == "=") {
                         if($valor_de[$condfield]==$condvalue) {
                             $this->field_no_edit[$field] = 0;
                         } else {
                             $this->field_no_edit[$field] = 1;
                         }
                     } else {
                         if($valor_de[$condfield]<>$condvalue) {
                             $this->field_no_edit[$field] = 0;
                         } else {
                             $this->field_no_edit[$field] = 1;
                         }
                     }
                }
                }
            }
            foreach ($rowresult as $field=>$value) {
                //Verifica los campos que estan seteados con NO editar, para no mostrarlos.
                if (!(array_key_exists($field, $this->field_no_edit) && $this->field_no_edit[$field] == 1 )){
                    $this->create_body_form($field,$value,"edit");

                //Fin del if, verifacdor de campos no edit.
                }
            //Fin Foreach
            }
            $req = $this->set_request('dbgrid_action','list',true);
            $req = $this->del_request($this->pk);
            echo "<div style='margin-top:5px; clear:both;'>
                <input type='submit' class='alertify-button alertify-button-ok' name='button_ok' value='".trans('Save')."'>
                <input type='submit' class='alertify-button alertify-button-cancel' name='button_cancel' value='".trans('Cancel')."' onClick='javascript:location=\"".SELF."$req\"; return false;'>
                </div>";
        //Fin de Row > 0 
        }
        echo "</fieldset></form>";
    }

    private function update_row($id) {
        $status="OK";
        $callbacks = !empty($this->update_callbacks);
        $update_fields=array();

        if(md5($this->salt.$id) <> $_POST['dbgrid_pkhash']) {
            $this->add_error("Trampa con el formulario");
        }

        $extra_query=Array();

        foreach ($this->structure as $field => $value){
            if (!(array_key_exists($field,$this->field_no_edit))){

                if(isset($this->structure[$field]['foreigntable'])) { 
                     $uptable = $this->structure[$field]['foreigntable'];
                     $foreign_key = $this->structure[$field]['foreignkey'];
                     foreach($_POST as $mkey=>$mval) {
                        if(preg_match("/^$field/",$mkey)) {
                            $extra_query[] = "REPLACE INTO $uptable SET $field='$mval' WHERE $foreign_key='$id'";
                        }
                     }
                } else {

                if (array_key_exists($field,$_POST)){

                    // Procesa validacion
                    if (!empty($this->validation_type[$field])) {
                        $this->check_validation_type($field,$_POST[$field]);
                    }

                    // Procesa callback de validacion
                    if (!empty($this->validation_callbacks[$field])) {
                        foreach ($this->validation_callbacks[$field] as $c) {
                            $_POST[$field] = call_user_func($c, $this, $_POST[$field],$_POST);
                        }
                    }
                    if ($field!=$this->pk) {
                        $update_fields[$field]=$_POST[$field];
                    }
                }
                if (array_key_exists($field,$_FILES)){
                    if ($field!=$this->pk) {
                        $update_fields[$field]=$_FILES[$field];
                    }
                }
                }
            }
        }

        $files_to_remove = Array();
        $files_added = Array();

        $queryfields=Array();
        $queryvars=Array();

        if (sizeof($update_fields)>0){
            $query = "UPDATE $this->table SET ";
            foreach ($update_fields as $field => $value){
                //echo "$field = $value<br>";
                if ($this->structure[$field]['input']=='img'){
                    $this->upload_img($id,$_FILES[$field],$field);
                    if(count($this->error)>0) { break; }
                    $value=$this->name_upload;
                    $files_added[] = $value;
                    if($this->old_name_upload <> '' && $this->old_name_upload<>$value ) {
                        $files_to_remove[] = $this->old_name_upload;
                    }
                } else if($this->structure[$field]['input']=='bitmask') {
                     $totbit=0;
                     foreach($value as $indv) {
                        $totbit+=$indv;
                     }
                     $value=$totbit;
                } 
                else if($this->structure[$field]['input']=='multiselect') {
                     $finvalue = implode(",",$value);
                     $value    = $finvalue;
                }
                $queryfields[]="%s='%s'";
                $queryvars[]="`$field`";
                $queryvars[]=$value;
                $this->del_request($field);
            }
            $query.=implode(",",$queryfields);
            $query.= " WHERE $this->pk = '%s' LIMIT 1";
            $queryvars[] = $id;
        }

        if(count($this->error)>0) {
            $this->edit_form( $id );
            return;
        }

        $res  = $this->link->consulta($query,$queryvars);

        if($res) {
           $this->add_notice(trans('Record updated'));
            if($callbacks) {
                $update_fields[$this->pk]=$id;
                foreach ($this->update_callbacks as $c) {
                    call_user_func($c, $update_fields);
                }
           }
           $this->unlink_files($files_to_remove);
        } else {
           // $this->set_request('dbgrid_error','Error al modificar registro',true);
           $this->add_error(trans('Error updating record'));
           $this->unlink_files($files_added);
        }

        $this->del_request($this->pk);
        $this->del_request('button_ok');
        $req=$this->set_request('dbgrid_action','list',true);
        //header("Location: ".SELF."$req");

//echo"<pre>";
//print_r($extra_query);

        $this->print_grid();
    }

    private function show_row($id){
        $query = "SELECT * FROM $this->table WHERE $this->pk = '$id'";
        $res = $this->link->consulta($query);
        echo "<fieldset>\n";
        echo "<legend>".trans('View Record')."</legend>\n";
        echo "\n";

        if ($this->link->num_rows() > 0) {
            $cont=0;
            foreach ($this->link->fetch_assoc($res) as $field=>$value) {
                if( !array_key_exists($field, $this->field_no_edit)) {
                    $this->create_body_form($field,$value,"show");
                }
                //Fin Foreach
            }
            $req = $this->set_request('dbgrid_action','list',false);
            echo "<div style='margin-top:5px; clear:both;'>";
            echo "<input type='submit' class='alertify-button alertify-button-cancel' name='button_cancel' onClick='javascript:location=\"".SELF."$req\"; return false;' value='".trans('Cancel')."'></div>";
        //Fin de Row > 0 
        }
        echo "</fieldset>";
    }

    private function delete_rows($ids,$customsalt='') {

        $cuantos_a_borrar = count($ids);
        $ids = implode(',', $ids);

        if($customsalt<>'') {
            $mihash = md5($this->salt.$customsalt);
        } else {
            $mihash = md5($this->salt.$ids);
        }
        $callbacks = !empty($this->delete_callbacks);
        $data = array();

        if($mihash <> $this->res_request['dbgrid_pkhash']) {
            $this->add_error("Trampa con el formulario ($ids)".$mihash);
        }

        if(count($this->error)>0)  {
            $this->del_request($this->pk);
            $this->set_request("dbgrid_action","list",true);
            $this->print_grid();
            return;
        }


        // Recupera datos para pasarle al delete callback y guarda nombre de archivos
        // a borrar si hubiera que borrarlos
        $files_to_remove = Array();

        $res = $this->link->consulta("SELECT %s FROM %s %s WHERE %s IN (%s)",$this->fields,$this->table,$this->join,$this->table.".".$this->pk,$ids);

        while($row = $this->link->fetch_assoc($res)) {
            $data[]=$row;
            foreach($row as $field=>$val) {
                if ($this->structure[$field]['input']=="img"){
                    $files_to_remove[] = $val;
                }
            }
        };

        $res = $this->link->consulta("DELETE FROM %s WHERE %s IN ($ids)",$this->table,$this->pk,$ids);

        if ($res AND $callbacks) {
            foreach ($data as $row) {
                foreach ($this->delete_callbacks as $c) {
                    call_user_func($c, $row);
                }
            }
        }

        if($res) {

            $this->unlink_files($files_to_remove);

            if($cuantos_a_borrar==1) {
                $this->set_request('dbgrid_notice',trans('Record deleted'),true);
            } else if ($cuantos_a_borrar>1) {
                $this->set_request('dbgrid_notice',trans('%s records deleted',$cuantos_a_borrar),true);
            }
        } else {
            $this->set_request('dbgrid_error',trans('Could not delete records'),true);
        }
        $this->del_request($this->pk);
        $this->del_request("button_ok");
        $req=$this->set_request('dbgrid_action','list',true);
        header("Location: ".SELF."$req");
    }

    private function create_body_form($field,$value,$type){

        switch ($type){
            case "edit":
                $atribute=(($field == $this->pk && !$this->modify_pk) || ( array_key_exists($field, $this->field_no_edit) && $this->field_no_edit[$field] == 1 )) ? 'disabled': '';
                break;
            case "show":
                $atribute="disabled";
                break;
            case "add":
                $atribute=($field == $this->pk && !$this->modify_pk) ? 'disabled': '';
                break;
            default:
                $atribute="disabled";
                break;
        }
        $value = $this->clean_entities($value);

        if(array_key_exists($field,$this->field_name)) {
            $display_name = $this->field_name[$field];
        }else{
            $display_name = $field;
        }

        $cadavalor=Array();
        if(isset($this->structure[$field]['explode'])) {
          $cadavalor=explode(",",$value);
        } else {
          $cadavalor[] = $value;
        }

        $cont=1;
        foreach ($cadavalor as $value) {
        if($cont>1) {
           $fieldp = $field.$cont;
           $display_name_p = $display_name." ".$cont;
        } else {
           $fieldp=$field;
           $display_name_p = $display_name;
        }
        if ($this->structure[$field]['input'] != 'hidden' ) {
            $parentstyle = isset($this->structure[$field]['parentstyle'])?$this->structure[$field]['parentstyle']:'';
            printf("\n<div %s>",$parentstyle);
        }
        if ($this->structure[$field]['input'] != 'hidden' && $this->structure[$field]['input'] != 'img' && $this->structure[$field]['input'] != 'select'){
            printf("<label for='%s'>%s</label>",$fieldp,$display_name_p);
        }

        $style = isset($this->structure[$field]['style'])?$this->structure[$field]['style']:'';

        switch ($this->structure[$field]['input']){
            case 'textarea':
                printf('<textarea %s name="%s" cols="50" rows="12"  %s>%s</textarea>',$style,$fieldp,$atribute,$value);
                break;
            case 'multiselect':
                printf('<select %s name="%s[]" %s size="7" multiple %s>',$style,$fieldp,$atribute);
                foreach ($this->structure[$field]['values'] as $k => $v) {
                    $coco = preg_split("/,/",html_entity_decode($value));
                    $caca = html_entity_decode($k);
                    $vprint = $this->get_edit_filtered($field,$v);
                    printf('<option value="%s" %s>%s</option>',$k,in_array($caca,$coco) ? 'selected' : '',$vprint);
                }
                echo '</select>';
                break;
 
            case 'select':
                printf("<label for='%s' class='labelselect'>%s<div>",$fieldp,$display_name_p);
                printf('<select %s name="%s" class="nooverlap" %s>',$style,$fieldp,$atribute);
                foreach ($this->structure[$field]['values'] as $k => $v) {
                    $vprint = $this->get_edit_filtered($field,$v);
                    printf('<option value="%s" %s>%s</option>',$k,(string)$k === $value ? 'selected' : '',$vprint);
                }
                echo '</select></div></label>';
                break;
            case 'bitmask':
                printf('<select name="%s[]" %s  size="7" multiple>',$fieldp,$atribute);
                foreach ($this->structure[$field]['values'] as $k => $v) {
                    printf("<option value=\"%s\" %s>%s</option>\n",$k,$k & $value ? 'selected' : '',$v);
                }
                echo '</select>';
                break;
            case 'date':
                $vprint = $this->get_edit_filtered($field,$value);
	            printf('<input type="text" name="%s" id="cal-%s" class="datePick" value="%s" %s />',$fieldp,$fieldp,$vprint,$atribute);
                break;
				/*
                    case 'time':
                        //printf('<input type="text" name="%s" value="%s"  %s/> <a href="javascript: void(document.forms[0].elements[\'%s\'].value = currentTime())" onclick="enableApply()" title="Click to set current time">Now</a>',
                        $field,
                        $value,
                        (($field == $this->pk && !$this->modify_pk) || ( array_key_exists($field, $this->field_no_edit) && $this->field_no_edit[$field] == 1 )) ? 'disabled': '',
                        $field);
                        break;
                    case 'datetime':
                        //printf('<input type="text" name="%s" value="%s"  %s/> <a href="javascript: void(document.forms[0].elements[\'%s\'].value = currentDateTime())" onclick="enableApply()" title="Click to set current date and time">Now</a>',
                        $field,
                        $value,
                        (($field == $this->pk && !$this->modify_pk) || ( array_key_exists($field, $this->field_no_edit) && $this->field_no_edit[$field] == 1 )) ? 'disabled': '',
                        $field);
                        break;
                        */
             case 'password':
                 printf('<input type="password" name="%s" value="%s" %s /><li><label>&nbsp;</label><input type="password" name="%s_confirm" value="%s" %s> <i>(confirmar)</i></li><li><label>&nbsp;</label><input type="checkbox" value="1" name="%s_blank" id="%s_blank"> <label for="%s_blank">Dejar clave en blanco?</label></li>',
                $fieldp,$value,$atribute,$fieldp,$value,$atribute,$fieldp,$fieldp,$fieldp);
                break;
             case 'hidden':
                printf('<input type="hidden" name="%s" value="%s" />',
                $fieldp,$value);
                 break;
             case 'img':
                  printf('<div class="inputfile">');
                  printf("<div class='fakefileLabel'>%s</div>&nbsp;",$display_name_p);
                  printf('<input type="file" onChange="document.getElementById(\'text-%s\').value=this.value;" name="%s" id="%s" %s/>',$fieldp,$fieldp,$fieldp,$atribute);
                  printf('<input class="fakefile submit" type="button" name="text-%s" id="text-%s" %s/ value="%s" onclick="routeClick(\"%s\")"></div>',$fieldp,$fieldp,$atribute,trans('Upload'),$fieldp);
                 if ($value != ""){
                    if(is_file($this->user_directory.$value)) {
                        printf('<p><img src="%s" alt="%s" title="%s" class="circle"></p>',$this->user_directory.$value,$value,$value);
                    }
                 }     
                 break;
             default:
                printf('<input %s type="text" name="%s" value="%s"  %s />',$style,$fieldp,$value,$atribute);
                break;
        }
        if ($this->structure[$field]['input'] != 'hidden' ) {
            echo "</div>\n";
        }
        $cont++;
        }
    }
            
    private function db_quote($str) {

            if (is_null($str)) {
                return 'NULL';
            }

            /**
            * Handle magic_quotes_gpc
            */
            if (ini_get('magic_quotes_gpc')) {
                $str = stripslashes($str);
            }

            return "'" . $this->link->escape_string($str) . "'";
        }

    private function unlink_files($files){
        if(is_array($files)) {
            foreach($files as $file) {
                unlink($this->user_directory.$file);
            }
        } else {
            unlink($this->user_directory.$files);
        }
    }

    private function upload_img($id,$arrFile,$field=""){
        $file = $arrFile['tmp_name'];
        if ($arrFile['size']>0 && !empty($file)) {
            if (is_uploaded_file($file)) {
                if (copy ($file, $this->user_directory.$id."-".$arrFile['name'])) {
                    $res = $this->link->consulta("SELECT %s FROM %s WHERE %s='%s' LIMIT 1",$field,$this->table,$this->pk,$id);
                    if($res) {
                        $row = $this->link->fetch_assoc($res);
                        $this->old_name_upload=$row[$field];
                    }
                    $this->name_upload=$id."-".$arrFile['name'];
                }else{
                    $this->add_error("No se pudo copiar el archivo");
                }
            }else{
                $this->add_error("No se pudo subir el archivo");
            }
        }else{
            $res=$this->link->consulta("SELECT %s FROM %s WHERE %s='%s' LIMIT 1",$field,$this->table,$this->pk,$id);
            if($res) {
               $row = $this->link->fetch_assoc($res);
               $this->name_upload=$row[$field];
            } else {
               $this->name_upload="";
            }

        }
    }

   /**
     * Verifica si un email tiene formato válido y opcionalmente verifica los registros MX del dominio tambien
     * 
     * @param String $email
     * @param Boolean $test_mx Verificar los registros MX del dominio
     */
    private static function isEmail($email, $test_mx = false){
        if($email=="") { return true; }
        if(preg_match("/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email)) {
            if($test_mx) {
                list( , $domain) = preg_split("/@/", $email);
                return getmxrr($domain, $mxrecords);
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Checks for a valid internet URL
     *
     * @param string $value The value to check
     * @return boolean TRUE if the value is a valid URL, FALSE if not
     */
    private static function isURL($value) {
        if (preg_match("/^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?$/i", $value)) {
            return true;
        } else {
            return false;
        }
    }

    private static function isAlfaNumeric($value) {
        if (preg_match("/^[A-Za-z0-9 ]+$/", $value)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isAlfa($value, $allow = '') {
        if (preg_match('/^[a-zA-Z' . $allow . ']+$/', $value)) {
            return true;
        } else {
            return false;
        }
    }

    private function search_rows(){
        /*$search=$_REQUEST['dbgrid_search'];
        $query_parts=array();
        foreach ($this->field_search as $key=>$val ){
            $query_parts[]= "$key LIKE '%%$search%%'";
        }
        $this->condition=implode(" OR ",$query_parts);*/
        $this->del_request('button_ok');
        $req=$this->set_request('dbgrid_action','list',true);
        $this->print_grid();
    }


//Fin de clase
}
