<?php
class View{
    static private $std_view = "MainView.html";
	
	function View($view=false, $subviews=array()){
		#: the view file
        if ($view !== false){
            $this->view_file = $view;
        } else {
            $this->view_file = View::$std_view;
        }
		#: assings subview names to files, fill with some defaults
		$this->subview_file_array = array_merge($subviews, array(
            "MENU" => "Menu.php",
            "LEFT_BOX" => "ProjectStatistics.php"));
        #: fill subviews direct with html code
        $this->subview_with_html = array();
		#: assings a field to a value (see set())
		$this->fields_array = array();
	}
	
	function set($field, $value){
		$this->fields_array[$field] = $value;
	}

    function fill_subview_with_html($subview, $html){
        $this->subview_with_html[$subview] = $html;
    }
	
	function render(){
		include('views/'.$this->view_file);
	}
	
	function get($field, $escape=True){
		$value = $this->fields_array[$field];
		if ($escape){
			$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', False);
		}
		return $value;
	}
	
	function render_subview($subview){
        if (in_array($subview, array_keys($this->subview_with_html))){
            echo $this->subview_with_html[$subview];
        } else {
        	if (in_array($subview, array_keys($this->subview_file_array))){;
    			include('views/subviews/'.$this->subview_file_array[$subview]);
        	} else {
        		print "Platzhalter für $subview";
        	}
        }
	}
}
