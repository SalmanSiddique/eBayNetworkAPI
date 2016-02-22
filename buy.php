<!--Full Name: Salman Vazirali Siddique, Student ID: 1001115361, URL: http://omega.uta.edu/~svs5361/project3/buy.php -->
<!DOCTYPE html>
<html>
<head><title>Buy Products</title>
</head>
<body>
    <div style="text-align: center;font-size: 20px">
<div style="width: 100%; margin: 0 auto;font-size: larger"><b>Programming Assignment 3: PHP Scripting (Developed by: Salman V. Siddique, UTA ID: 1001115361)</b></div>
        <hr>
</div>
<?php
    session_start();
    $searchresults = array();
    $cart = array();
    $total=0;
    $xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
    $xml = new SimpleXMLElement($xmlstr);
    $arr = array();
        class Combo
        {
            var $id;
            var $name;
            var $isgroup;
            function Combo($a,$b,$c="f")
            {
                global $arr;
                $this->id=$a;
                $this->name=$b;
                $this->isgroup=$c;
                array_push($arr,$this);
            }
        }
        class SearchResponse
        {
            var $id;
            var $name;
            var $minprice;
            var $imgurl;
            var $offersurl;
            function SearchResponse($a,$b,$c,$d,$e)
            {
                global $searchresults;
                $this->id=$a;
                $this->name=$b;
                $this->minprice=$c;
                $this->imgurl=$d;
                $this->offersurl=$e;
                //array_push($searchresults,$this);
                //print_r($this);
                return $this;
            }
        }
        foreach ($xml->category as $cat)
        {
            $com = new Combo((String)$cat['id'],(String)$cat->name,'t');
            foreach ($cat->categories->category as $cat1)
            {
                $com = new Combo((String)$cat1['id'],(String)$cat1->name,'t');
                if($cat1->categories)
                    {
                        foreach ($cat1->categories->category as $incat)
                        {
                            $com = new Combo((String)$incat['id'],(String)$incat->name,'f');
                        }
                    }
            } 
        }
          
        if (isset($_GET['clear'])) {
            global $cart;
            $cart = $_SESSION['cart'];
            $val = $_GET['clear'];
                if($val==1)
                {
                   foreach($cart as $delkey => $delob)
                   {
                        if(isset($delob))
                        {
                            unset($cart[$delkey]);
                        }
                   }

                   /*for($r=0;$r<count($cart);$r++)
                   {
                        unset($cart[$r]);
                   }*/
                   $_SESSION['cart'] = $cart;;
                }
        }

        if (isset($_GET['delete'])) {
               global $cart;
               $cart = $_SESSION['cart'];
               asort($cart);
               $delid = $_GET['delete'];
//               echo '<br>before: '.print_r($cart).'</br>';
               foreach($cart as $delkey => $delob)
               {
                   if(isset($delob))
                   {
                       if($delob->id==$delid)
                       {
                           //$delob=NULL;
                           unset($cart[$delkey]);
                       }
                   }
               }
//               echo '<br>after: '.print_r($cart).'</br>';
               $_SESSION['cart'] = $cart;
          }

        if (isset($_GET['buy'])) {
            $buyid = $_GET['buy'];
            $searchresults = $_SESSION['search'];
            global $cart;
            if(isset($_SESSION['cart']))
            {
                $cart = $_SESSION['cart'];    
            }
            foreach($searchresults as $curobj)
            {
                if($curobj->id==$buyid)
                {
                    $ex = FALSE;
                    foreach($cart as $m)
                    {
                        if($m->id==$buyid)
                        {
                            $ex = TRUE;
                        }
                    }
                    if($ex==FALSE)
                    {
                        array_push($cart,$curobj);    
                    }
                }
            }
            $_SESSION['cart'] = $cart;
        }

        if(isset($_SESSION['cart']))
            {
                $a = $_SESSION['cart'];
                if(count($a)>0)
                {
                    $data='</br></br><div style="text-align: center;font-size: 25px"><b>Shopping Cart</b></div></br><table id="cart" border="1" width="100%" align="center"><tr><th><b>Product Image & URL</b></th><th><b>Product Name</b></th><th><b>Product Price</b></th><th><b>Actions</b></th></tr>';
                    foreach($a as $curob)
                    {
                        global $total;
                        $data.='<tr><td align="center"><a href='.$curob->offersurl.'"><img src="'.$curob->imgurl.'"/></a></td><td align="center">'.$curob->name.'</td><td align="center">$'.$curob->minprice.'</td><td align="center"><a href=buy.php?delete='.$curob->id.'>Remove</a></td></tr>';
                        $total+=(Float)$curob->minprice;
                    }
                    $data.='<tr><td colspan="2" align="center"><b>Total Payment Due</b></td><td align="center" colspan="2"><b>$'.$total.'</b></td></tr></table>';
                    echo $data;
                }
            }
        ?>
<form action="buy.php" method="GET">
    <input type="hidden" name="clear" value="1"/>
    </br>
    <div id="show_button" style="display: block;text-align: center">
       <input type="submit" value="Empty Shopping Cart"/>
    </div>
</br></br>
</form>
<form action="buy.php" onsubmit="test()" onload="test()" method="GET">
<fieldset><legend>Search</legend><div style="width:100%;text-align: center;font-size: large">Please select the category and provide the keywords</div></br><div style="width:100%;text-align: center;">
<label>Category: <select name="category"><?php 
    foreach($arr as $curobj)
    {
        if($curobj->isgroup=="t")
        {
            echo "<option value='".$curobj->id."'>".$curobj->name."</option><optgroup label='".$curobj->name."'>";
        }
        else
        {
            echo "<option value='".$curobj->id."'>".$curobj->name."</option>";
        } 
    }
?></select></label>&nbsp;&nbsp;&nbsp;
<label>Search Keywords: </label><input type="text" style="width: 350px" name="search"/>
<input type="submit" value="Search"/></div>
</fieldset>
</form>
    <?php
        if (isset($_GET['category'],$_GET['search'])) 
        {
       $url = 'http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&category='.urlencode($_GET['category']).'&keyword='.urlencode($_GET['search']).'&numItems=20';
       $xmlstr1 = file_get_contents($url);
       $xml1 = new SimpleXMLElement($xmlstr1);
       $txt='</br></br><div style="text-align: center;font-size: 25px"><b>Search Results</b></div></br><table id="results" border="1" width="100%" align="center"><tr><th><b>Product Image & URL</b></th><th><b>Product Name</b></th><th><b>Product Price</b></th><th><b>Product Description</b></th></tr>';
       if(isset($xml1->categories->category->items->product))
       {
       foreach ($xml1->categories->category->items->product as $prod)
           {
               global $searchresults;
               $txt.='<tr><td align="center"><a href="buy.php?buy='.$prod['id'].'"><img src="'.$prod->images->image->sourceURL.'"/></a></td><td align="center">'.$prod->name.'</td><td align="center">$'.$prod->minPrice.'</td><td align="center">'.$prod->fullDescription.'</td></tr>';
               $re1 = new SearchResponse((String)$prod['id'],(String)$prod->name,(String)$prod->minPrice,(String)$prod->images->image->sourceURL,(String)$prod->productOffersURL);
               array_push($searchresults,$re1);
           }
       }
       $_SESSION['search'] = $searchresults;
       $txt.='</table>';
       echo $txt;
}
    ?>
</body>
</html>
