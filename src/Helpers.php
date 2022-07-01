<?php

namespace App;

class Helpers
{
    public function availabilityCheck($text):bool{
        $search = 'In Stock';
        return stristr($search,substr($text,0,8)) == true;
    }

    public function expandByColor(array $list):array{
        $products = [];
        foreach ($list as $product){
            foreach ($product->colour as $colour){
                $productObject = (object)[
                    "title" => $product->title,
                    "price" => $product->price,
                    "colour" => $colour,
                    "imageUrl" => $product->imageUrl,
                    "capacityMB" => $product->capacityMB,
                    "availabilityText" => $product->availabilityText,
                    "isAvailable" => $product->isAvailable,
                    "shippingDate" => $product->shippingDate
                ];
                $products[] = $productObject;
            }
        }
        return $products;
    }

    public function shippingDate($text, $text2):string{
        if ($text == $text2){
            return "0000-00-00";
        }else{
            if ($text2 == "Unavailable for delivery"){
                return "0000-00-00";
            }elseif($text2 =="Free Shipping" || $text2 == "Free Delivery"){
                //if free shipping return today's date
                return date('Y-m-d');
            }elseif($text2 == "Free Delivery tomorrow"){
                return date('Y-m-d', strtotime("+1 day"));
            }elseif(str_contains($text2, "Delivery by") == true){
                return date("Y-m-d", strtotime(substr($text2,12)));
            }elseif(str_contains($text2, "Delivery from") == true){
                return date("Y-m-d", strtotime(substr($text2,13)));
            }elseif (str_contains($text2,"Delivers") == true){
                return date("Y-m-d", strtotime(substr($text2,9)));
            }
            elseif (str_contains($text2,"Free Delivery") == true){
                return date("Y-m-d", strtotime(substr($text2,13)));
            }elseif(str_contains($text2, "Order within")){
                return date("Y-m-d", strtotime(substr($text2,-11)));
            }
            else{
                return $text2;
            }
        }
    }

    public function uniqueProducts($list): array
    {
        $products = $this->expandByColor($list);
        return  array_values(array_unique($products, SORT_REGULAR));
    }

    public function capacityFormat($text): string{
        //remove whitespace
        $strippedText = str_replace(' ', '', $text);
        //select the either MB OR GB
        $capacityText = substr($strippedText, -2);
        $len = strlen($strippedText) - 2;
        if ($capacityText === "MB"){
            $formattedCapacity = (int)substr($strippedText, 0,$len);
        }else{
            //convert to MB
            $formattedCapacity = (int)substr($strippedText, 0,$len) * 1000;
        }
        return $formattedCapacity;
    }
}