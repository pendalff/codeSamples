<?php
class Tool_View_Href {
	
	private $pathContext = '';
	private $pathQueryParameters = array();
	
	/**
	 *
	 * @param string $context context path, script filename
	 * @param array $pathQueryParameters path query parameters
	 */
	public function __construct($pathContext, $pathQueryParameters=array()) {
		$this->pathContext = $pathContext;
		$this->pathQueryParameters = $pathQueryParameters;
	}

	public function __toString(){
		return $this->pathContext . '?' . http_build_query($this->pathQueryParameters);
	}

}
class DB_ClosureTable_Render {

	private $node;
	private $root;
	private $tree;
	private $lrValues;
	private $columns;
	
	private $server;
	private $self;

	public function __construct(
		array $node,
		$selfScript = "",
		$serverScript="server",
		$selfScriptExt=".php",
		$serverScriptExt=".php"
	) {

		if(
			array_key_exists("root", $node) and
			array_key_exists("columns", $node) and
			array_key_exists("LRValues", $node) ) {

			$this->root = $node["root"];
			$this->tree = $node[$node["root"]];
			$this->node = $node;
			$this->lrValues = $node["LRValues"];
			$this->columns = $node["columns"];
			$this->server = $serverScript . $serverScriptExt;
			$this->self = $selfScript . $selfScriptExt;

		} else {
			throw new UnexpectedValueException("missing key: root, columns, LRValues expected.");
		}
	}

	public static function getChilds(array $node) {
		$cnodes = array();
		//$a = new self;
		$columns= isset($node["columns"]) ? $node["columns"] : array ( 'captionField' => 'post', 'idField' => 'descendant', 'parentIdField' => 'parent', 'childNodesField' => 'childs', 'leafNodesField' => 'isLeaf', 'weightField' => 'weight', 'leftField' => 'l', 'rightField' => 'r', 'counterField' => 'c', );

		if  (array_key_exists($columns["childNodesField"], $node)) {

			foreach ($node[$columns["childNodesField"]] as $cnode) {
				
				if ($cnode[$columns["parentIdField"]] !== null) {
					$cnode['columns'] = $columns;
					$cnodes[] = $cnode;
				}
			}
		}
		elseif(array_key_exists('root', $node)) {

			foreach ( $node[$node['root']][$columns["childNodesField"]] as $cnode) {
	
				if ($cnode[$columns["parentIdField"]] !== null) {
					$cnode['columns'] = $columns;
					$cnodes[] = $cnode;
				}
			}
		}
	
		return $cnodes;
	}

	private function getChild($node) {
		$cnodes = array();
		if(array_key_exists($this->columns["childNodesField"], $node)) {
			foreach ($node[$this->columns["childNodesField"]] as $cnode) {
				if ($cnode[$this->columns["parentIdField"]] !== null) {
					$cnodes[] = $cnode;
				}
			}
		}
		return $cnodes;
	}

	private function getLeftValue($node) {
		return $this->lrValues[$this->columns["leftField"]][$node[$this->columns["idField"]]];
	}

	private function getRightValue($node) {
		return $this->lrValues[$this->columns["rightField"]][$node[$this->columns["idField"]]];
	}

	private function getParentLeftValue($node) {
		return $this->lrValues[$this->columns["leftField"]][$node[$this->columns["parentIdField"]]];
	}

	private function getParentRightValue($node) {
		return $this->lrValues[$this->columns["rightField"]][$node[$this->columns["parentIdField"]]];
	}

	private function renderTree($node, $type = "ul", $level=0, $identChar=" ", $pleft=0, $pright=0, $prevIdField=null) {

		// useless but shows the nested set values
		//$l = $this->getLeftValue($node);
		//$r = $this->getRightValue($node);

		$lineBreak = "\n";

		$title = '<div style="width:200px;float:left;">';
		# ns left
		$hrefDown = Url::site( Route::get('forum')->uri( array(  'action' => 'topic', "id" => $node[$this->columns["idField"]] )));
		
		$title  .= '<a href="'. $hrefDown .'">';
		$title  .= '<span style="color:red;font-size:0.5em;vertical-align: text-top;">D</span>';
		$title  .= '</a> | ';

		$hrefAdd = Url::site( Route::get('forum_edit')->uri( array(  'action' => 'addpost', "forum" => $node['forum_id'], "id" => $node[$this->columns["idField"]] )));

		$title  .= '<a href="'. $hrefAdd.'">'.$node[$this->columns["idField"]];
		//$title  .="|".$node[$this->columns["captionField"]];
		$title  .='</a> ';

		if($node[$this->columns["parentIdField"]]!==null){
			$hrefParent = Url::site( Route::get('forum')->uri( array(  'action' => 'topic', "node" => $node[$this->columns["idField"]] )));
			$title  .= ' | <a href="'. $hrefParent .'"><span style="color:red;font-size:0.5em;vertical-align: text-top;">';
		}

		$title  .= '<span style="color:green;font-size:0.5em;vertical-align: text-top">R</span>';
		if($node[$this->columns["parentIdField"]]!==null){
			$title  .= '</a>';
		}

		
		$title .= '</div>';
		$title .= '<div style="float:left;">';

		# left
		if($this->root != $node[$this->columns["parentIdField"]] and $node[$this->columns["idField"]]!=$this->root and $node[$this->columns["leafNodesField"]]==1) {
	//		$hrefLeft = (string) new Tool_View_Href($this->server, array("perm"=>"left", "a"=>$node[$this->columns["idField"]], "p"=>$node[$this->columns["parentIdField"]], "node"=>$_GET["node"]) );
	//		$title  .= '<a href="'. $hrefLeft.'"><img src="../../img/node_move_left.png" alt="Left" title="Left" border="0"/></a> ';
		} elseif($this->root != $node[$this->columns["idField"]]) {
	//		$title  .= '<img src="../../img/node_move_left_gray.png" alt="Left" title="Left" border="0"/> ';
		}
	
		# up
		if($this->root != $node[$this->columns["idField"]] and $node[$this->columns["weightField"]] >1) {
	//		$hrefUp = (string) new Tool_View_Href($this->server, array("perm"=>"up", "a"=>$node[$this->columns["idField"]], "p"=>$node[$this->columns["parentIdField"]], "node"=>$_GET["node"]) );
	//		$title  .= '<a href="'. $hrefUp.'"><img src="../../img/node_move_up.png" alt="Up" title="Up" border="0"/></a> ';
		} elseif($this->root != $node[$this->columns["idField"]]) {
	//		$title  .= '<img src="../../img/node_move_up_gray.png" alt="Up" title="Up" border="0"/> ';
		}

		# down
		if($this->root != $node[$this->columns["idField"]] and $node[$this->columns["weightField"]] < count($this->node[$node[$this->columns["parentIdField"]]]["childs"])) {
	//		$hrefDown = (string) new Tool_View_Href($this->server, array("perm"=>"down", "a"=>$node[$this->columns["idField"]], "p"=>$node[$this->columns["parentIdField"]], "node"=>$_GET["node"]) );
	//		$title  .= '<a href="'. $hrefDown.'"><img src="../../img/node_move_down.png" alt="Down" title="Down" border="0"/></a> ';
		} elseif($this->root != $node[$this->columns["idField"]]) {
	//		$title  .= '<img src="../../img/node_move_down_gray.png" alt="Down" title="Down" border="0"/> ';
		}

		# right	
		if($node[$this->columns["idField"]]!=$this->root and $node[$this->columns["weightField"]] > 1 and $node[$this->columns["leafNodesField"]]==1) {
	//		$hrefRight = (string) new Tool_View_Href($this->server, array("perm"=>"right", "a"=>$node[$this->columns["idField"]], "p"=>$prevIdField, "node"=>$_GET["node"]) );
	//		$title  .= '<a href="'. $hrefRight.'"><img src="../../img/node_move_right.png" alt="Right" title="Right" border="0"/></a> ';
		} elseif($this->root != $node[$this->columns["idField"]]) {
	//		$title  .= '<img src="../../img/node_move_right_gray.png" alt="Right" title="Right" border="0"/> ';
		}

		# delete
		if($node[$this->columns["idField"]]!=$this->root and $node[$this->columns["leafNodesField"]]==1){
//			$delLeaf = (string) new Tool_View_Href($this->server, array("perm"=>"del", "a"=>$node[$this->columns["idField"]], "p"=>$node[$this->columns["parentIdField"]], "node" => $node["node"]));
//			$title  .= ' <a href="'. $delLeaf .'"><img src="../../img/node_delete.png" alt="Delete" title="Delete" border="0"/></a>';
		}elseif(
			$node[$this->columns["leafNodesField"]]>1 and
			!is_null($node[$this->columns["parentIdField"]]) and
			(empty($_GET["node"]) or $this->root!=$node[$this->columns["idField"]])
		){
//			$remSubtree = (string) new Tool_View_Href($this->server, array("perm"=>"rem", "a"=>$node[$this->columns["idField"]], "p"=>$node[$this->columns["parentIdField"]], "node" => $node["node"]));
	//		$title  .= ' <a href="'. $remSubtree.'"><img src="../../img/node_delete_tree.png" alt="Delete Subtree" title="Delete Subtree" border="0"/></a>';
		}

		$title .= '</div><div style="clear:both;"></div>';

		$cnodes = $this->getChild($node);
		if (count($cnodes) > 0) {
			$out = "";
			if($level==0){
				$out .= '<'.$type.'>'.$lineBreak;
			}
			$out .= str_repeat($identChar, $level+1).'<li><div>';
			$out .= $title . $lineBreak;
			$out .= str_repeat($identChar, $level+2).'</div><'.$type.'>'.$lineBreak;

			foreach ($cnodes as $cnode) {
				$out .= $this->renderTree($cnode, $type, $level+1, $identChar, $pleft, $pright, $node[$this->columns["idField"]]);
	//			$l++;
			}				

			$out .= str_repeat($identChar, $level+2). '</'.$type.'>' . $lineBreak;
			$out .= str_repeat($identChar, $level+1). '</li>' . $lineBreak;
			if($level==0){
				$out .= str_repeat($identChar, $level). '</'.$type.'>'.$lineBreak;
			}
			return $out;
			
		} else {
			return str_repeat($identChar, $level+2) .'<li><div>'. $title .'</div></li>'.$lineBreak;
		}

	}
	
	public function render() {
		return $this->renderTree($this->tree);
	}
	
	public function __toString() {
		return $this->renderTree($this->tree);
	}
}
