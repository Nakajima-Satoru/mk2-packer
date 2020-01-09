<?php

/*

- mk2 standard packer -

PaginatePacker

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class PaginatePackerUI extends PackerUI{

	# output

	public function output($paginate){

		$str="";
		if($paginate->totalPage>1){

			$str.='<nav aria-label="Page navigation example">';
			$str.='<ul class="pagination justify-content-center">';
			
			if($paginate->page>1){
				$str.='<li class="page-item"><a class="page-link" href="?page='.($paginate->page-1).'">Previous</a></li>';
			}
			for($n1=0;$n1<$paginate->totalPage;$n1++){
				$active="";
				if($paginate->page==($n1+1)){
					$active.=' active';
				}
				$str.='<li class="page-item'.$active.'"><a class="page-link" href="?page='.($n1+1).'">'.($n1+1).'</a></li>';
			}
			if($paginate->page<$paginate->totalPage){
				$str.='<li class="page-item"><a class="page-link" href="?page='.($paginate->page+1).'">Next</a></li>';
			}

			$str.='</ul>';
			$str.='</nav>';

			return $str;
		}

	}

}