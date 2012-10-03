<?php 
$monthCode = array('0','F','G','H','J','K','M','N','Q','U','V','X','Z');
Class yahoo 
{ 
	
	function get_stock_quote($symbol) 
	{ 			
		ini_set('user_agent', 'Mozilla/5.0 (compatible; HttpAnalyzer/0.5; +xxxx@gmail.com');	
		error_reporting(0);							
		/*
		 * o = open
		 * h = high
		 * g = low
		 * c = change (c1)
		 * v = volume
		 * r = range?
		 * b = bid?
		 * p2 = percent change
		 * 
		 */					 
		$url = sprintf("http://finance.yahoo.com/d/quotes.csv?s=%s&f=sl1d1t1c1ohgvp2" ,$symbol); 
		try {
		$fp = fopen($url, "r"); 
		} catch(Exception $e) {
			//$fp = fopen($url, "r"); //try again!
			//die($e->getMessage());
		}
		
		if(!$fp) 
		{ 
			$i=0;
			while(!$fp && $i < 5) {
				$fp = fopen($url, "r");
				$i++;
			}
		}
		if(!$fp) {
			echo "error : cannot recieve stock quote information"; 
		} 
		else 
		{ 
			$array = fgetcsv($fp , 4096 , ', '); 
			fclose($fp); 
			$this->symbol = $array[0]; 
			$this->last = $array[1]; 
			$this->date = $array[2]; 
			$this->time = $array[3]; 
			$this->change = $array[4]; 
			$this->open = $array[5]; 
			$this->high = $array[6]; 
			$this->low = $array[7]; 
			$this->volume = $array[8]; 
			$this->percentChange = $array[9];
			
			//echo var_dump($array);
		} 
	} //end func
}//end class
// basic chart = http://ichart.finance.yahoo.com/z?s=CLF10.NYM&t=1d&q=c&l=on&z=m&a=v&p=s
/*
<h3 style="color: #88ea88; margin-bottom: 0px; padding-bottom:0px">Free</h3> 
<div style="font-family: sans-serif; font-size: 25px; color: #88ea88; margin-top: 0px; padding-top:0px">Streaming Market Quotes</div>
*/

?>
<style type="text/css">
.rightPanel {
background-color: #99ee99 !important;
}
</style>
<div class="middlecontent" style="width: 300px;">
<?php 
require("dbConn.php");

$quote = new yahoo; //get the dow for the date header
$quote->get_stock_quote("^DJI"); //oic = CLF10.NYM
?>
<!--
<span class="toptextdate"><?php // echo $quote->date." ";//$quote->time; ?></span> -->

<table class="middlecontentleftbottom" border='0'><tr><td colspan="4">
<big><strong>Streaming Quotes: Real Time Data on Refresh</strong></big><br />
<a style="cursor:pointer;"  onclick="getNewContent('/scripts/yahoo_stocks.php','stock_frame');">Click to Refresh Data</a></td></tr>

<tr style="background-color: #fff;" class="new">
<td  width="100%" align="left" valign="top">
<?php 
$sql = "SELECT * FROM stock_category WHERE (parent IS NULL OR parent = 0) AND published = 1 ORDER BY ordering";
$result = mysql_query($sql) or die(mysql_error());
$blockCount = 1;
while ($main_cat = mysql_fetch_object($result)) {
	if($blockCount == 3) { //end of row
		$blockCount = 1;
		echo '</tr><tr style="background-color: #fff;" class="new">';
	}
?>
	
<!-- <table class="middlecontentleftbottom" border='0' > -->
	<tr>
		<td valign="middle" width="140px"  style="text-align: left;" class="stock_maincat_title"><?php echo $main_cat->name; ?></td>
		<td align="right" valign="middle" width="76px"  >Last</td>
		<td align="right" valign="middle" width="76px" >Change</td>
		<td align="right" valign="middle" width="76px" >Change%</td>
	</tr>
	<?php 
	$sql = "SELECT * FROM stock_category WHERE parent = ".$main_cat->stock_category_id." AND published = 1 ORDER BY ordering";
	$subResult = mysql_query($sql) or die(mysql_error());
	if(!mysql_num_rows($subResult)) {
		$query = "SELECT * FROM stock_symbol WHERE stock_category_id = ".$main_cat->stock_category_id." AND `show` = 1 ORDER BY ordering";
		$stockResult = mysql_query($query) or die(mysql_error()."<br />$query");
		while($index = mysql_fetch_object($stockResult)) {
			$quote = new yahoo;
			$quote->get_stock_quote($index->symbol);
			
			$tooltip = "http://ichart.finance.yahoo.com/z?s=".$index->symbol. "&t=1d&q=c&l=on&z=s&a=v&p=s";
			//$tooltip = "http://finviz.com/chart.ashx?t=".$index->symbol. "&ty=c&ta=0&p=d&s=l";
			$tooltiplarge = "http://ichart.finance.yahoo.com/z?s=".$index->symbol. "&t=1d&q=c&l=on&z=l&a=v&p=s";
			?>
			<tr>
			<td align="left" valign="middle" width="76px">
			<span class="comanyname">&nbsp;&nbsp;
			<a href="javascript:Tip('<img src=\'<?php echo $tooltiplarge; ?>\' />', STICKY, true, CLOSEBTN, true, CLICKCLOSE, true, WIDTH, 800 )" 
			onmouseover="Tip('<img src=\'<?php echo $tooltip; ?>\' width=\'300\'/>', WIDTH, 300, DELAY, 800 )" onmouseout="UnTip()">
			<?php echo $index->name; ?></a>
			
			</span></td>
			<td align="right" valign="middle" width="76px"><span class="lasttext"><?php echo $quote->last; ?></span></td>
			<td align="right" valign="middle" width="76px">
			<span 
			<?php if(substr($quote->change,0,1 ) == "-") echo ' style="color: red;"; '?>
			class="changetext"><?php echo $quote->change ? $quote->change : '0'; ?></span></td>
			<td align="right" valign="middle" width="76px">
			<span 
			<?php if(substr($quote->percentChange,0,1 ) == "-") echo ' style="color: red;"; '?>
			class="change2text"><?php echo $quote->percentChange; ?></span></td>
			<td align="center" valign="middle" width="16px"></td>
			</tr>
			<?php 
		}//end while - show individual link for stock symbol
		
	}// end if numrows was zero (no child categories)
	else { 
		while($subCat = mysql_fetch_object($subResult)) {
		?>
		<tr>
		<td colspan='4' class="stock_subcat_title"><?php echo $subCat->name; ?></td>
		</tr>
	<?php 	
	$query = "SELECT * FROM stock_symbol WHERE stock_category_id = ".$subCat->stock_category_id." AND `show` = 1 ORDER BY ordering";
		$stockResult = mysql_query($query) or die(mysql_error()."<br />$query");
		while($index = mysql_fetch_object($stockResult)) {
			$symbol = $index->symbol;
			
			if($index->periodic && $index->e_mini) { //we want 2months ahead of the 3rd friday of last month
				$lastMonthTS =  strtotime("01-". (date('n') - 1)."-".date("Y"));
				$fris = 0;
				while($fris < 3 ) {
					$lastMonthTS += 86400;
					if(date('D',$lastMonthTS) == "Fri")
						$fris++;
				}
				
				$pd_index = explode(".",$index->symbol);
				$monthNum = date('n',$lastMonthTS) + $index->advance_months;
				$yearNum = date('y');
				if($monthNum == 13) {
					$monthNum = 1;
					$yearNum++;
				}
					
				$symbol = $pd_index[0].$monthCode[$monthNum].$yearNum.".".$pd_index[1]; //e.g. CLF10.NYM
			} elseif($index->periodic) {
				$pd_index = explode(".",$index->symbol);
				$monthNum = date('n') + $index->advance_months;
				
				$yearNum = date('y');
				if($monthNum == 13) {
					$monthNum = 1;
					$yearNum++;
				}
			
				$symbol = $pd_index[0].$monthCode[$monthNum].$yearNum.".".$pd_index[1]; //e.g. CLF10.NYM
			}
			
			$quote = new yahoo;
			$quote->get_stock_quote($symbol);
			
			if((string)$quote->last == "0.00" && $index->e_mini ) {
				$monthNum = date('m'); //set month number to now
				// first try ahead one month
				$symbol = $pd_index[0].$monthCode[$monthNum].$yearNum.".".$pd_index[1];
				$quote = new yahoo;
				$quote->get_stock_quote($symbol);
				
			}
			
			while((string)$quote->last == "0.00" && $index->e_mini && $monthNum <= (date('m') + $index->advance_months + 6) ) {
				$monthNum++; // monthNum can get as high as 18 - if it's past 12, subtract it next
				
				if($monthNum > 12)
					$futureMonth =  $monthNum - 12;
				else
					$futureMonth =  $monthNum;
				
				$symbol = $pd_index[0].$monthCode[$futureMonth].$yearNum.".".$pd_index[1];
				$quote = new yahoo;
				$quote->get_stock_quote($symbol);
				
			}
			
			//if still zero, try going back one month
			if((string)$quote->last == "0.00" && $index->e_mini ) {
				$monthNum = date('m') - 1;
				if($monthNum < 1)
				$monthNum = 12;
				
				$symbol = $pd_index[0].$monthCode[$monthNum].$yearNum.".".$pd_index[1];
				$quote = new yahoo;
				$quote->get_stock_quote($symbol);
				
			}
			
			//if STILL zero, try going back to current month & year
			if((string)$quote->last == "0.00" && $index->e_mini ) {
				$monthNum = date('n');
				$yearNum = date('y');
				$symbol = $pd_index[0].$monthCode[$monthNum].$yearNum.".".$pd_index[1];
				$quote = new yahoo;
				$quote->get_stock_quote($symbol);
				
			}
			
			if(!$index->chartname) {
			$tooltip = "http://ichart.finance.yahoo.com/z?s=".$symbol. "&t=1d&q=c&l=on&z=s&a=v&p=s";
			//$tooltip = "http://finviz.com/chart.ashx?t=".$index->symbol. "&ty=c&ta=0&p=d&s=l";
			$tooltiplarge = "http://ichart.finance.yahoo.com/z?s=".$symbol. "&t=1d&q=c&l=on&z=l&a=v&p=s";
			} else {
			$tooltip = "http://ichart.finance.yahoo.com/z?s=".$index->chartname. "&t=1d&q=c&l=on&z=s&a=v&p=s";
			//$tooltip = "http://finviz.com/chart.ashx?t=".$index->symbol. "&ty=c&ta=0&p=d&s=l";
			$tooltiplarge = "http://ichart.finance.yahoo.com/z?s=".$index->chartname. "&t=1d&q=c&l=on&z=l&a=v&p=s";
				
			}
			?>
			<tr>
			<td align="left" valign="middle" width="76px">
			<span class="comanyname">&nbsp;&nbsp;
			<a href="javascript:Tip('<img src=\'<?php echo $tooltiplarge; ?>\' />', STICKY, true, CLOSEBTN, true, CLICKCLOSE, true, WIDTH, 800 )" 
			onmouseover="Tip('<img src=\'<?php echo $tooltip; ?>\' width=\'300\'/>', WIDTH, 300, DELAY, 800 )" onmouseout="UnTip()">
			<?php echo $index->name; ?></a>
			</span></td>
			<td align="right" valign="middle" width="76px"><span class="lasttext"><?php echo $quote->last; ?></span></td>
			<td align="right" valign="middle" width="76px">
			<span 
			<?php if(substr($quote->change,0,1 ) == "-") echo ' style="color: red;"; '?>
			class="changetext"><?php echo $quote->change; ?></span></td>
			<td align="right" valign="middle" width="76px">
			<span 
			<?php if(substr($quote->percentChange,0,1 ) == "-") echo ' style="color: red;"; '?>
			class="change2text"><?php echo $quote->percentChange; ?></span></td>
			<td align="center" valign="middle" width="16px"></td>
			</tr>
		<?php }//end while (display a single row of stock symbol data 
		}//end while - fetch each subcatgegory and display it's stock data
	}// end else (stocks within a subcategory)
	
	// </table>

$blockCount++;
}//end while (maincat block)
?>
</td>
</tr></table>
</div>
