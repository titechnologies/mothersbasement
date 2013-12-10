<?php
set_time_limit(0);  
error_reporting(0);
require_once '/home/mothersb/public_html/app/Mage.php';  
umask(0);  
$indexer="/home/mothersb/public_html/shell/indexer.php";  

    //indexing starts
    if(file_exists($indexer)){  
		$idxlist = array("catalog_product_attribute",  
					   "catalog_product_price",  
					   "catalog_url",
					   "catalog_product_flat",  
					   "catalog_category_flat",  
					   "catalog_category_product",  
					   "catalogsearch_fulltext",  
					   "cataloginventory_stock",
					   "tag_summary",
					   "mana_db_replicator",
					   "rewards_transfer");  
		//reindex using magento command line  
		foreach($idxlist as $idx){
			//echo "reindex $idx n ";  
			exec("php /home/mothersb/public_html/shell/indexer.php --reindex $idx");
			Mage::log("Finished Rebuilding ".$idx." At: " . date("d/m/y h:i:s")); 
		}  
	}  
?>