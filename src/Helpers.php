<?php

namespace App;

class Helpers
{
    public function availabilityCheck($text):bool{
        $search = 'In Stock';
        return stristr($search,substr($text,0,8)) == true;
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

    public function duplicateIndexes(array $list):array{
        $temp = [];
        $indexes = [];
        foreach ($list as $key=> $item){
            //define product with values that make it a duplicate.
            $product = new Product();
            $product->title = $item->title;
            $product->capacityMB = $item->capacityMB;
            $product->price = $item->price;
            //check if current iterated product is in array, if so save index values
            if (in_array($product, $temp)){
                $indexes[] = [$key, array_search($product, $temp)];
            }
            $temp[] = $product;
        }
        return $indexes;
    }

    public function duplicateProducts(array $list):array{
        $indexes = $this->duplicateIndexes($list);
        $duplicates = [];
        //find duplicates from original list and return
        foreach ($indexes as $index){
            foreach ($index as $value){
                $duplicates[] = $list[$value];
            }

        }
        return $duplicates;
    }

    public function uniqueProducts($list): array
    {
        $indexes = $this->duplicateIndexes($list);
        //remove all duplicates
        $duplicates = $this->duplicateProducts($list);
        foreach ($duplicates as $product){
            $index = array_search($product, $list);
            array_splice($list, $index, 1);
        }
//        //append first item from the two from each array of duplication
        foreach ($indexes as $index){
            $list[] = $duplicates[0];
        }

        return $list;
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

    public function color(array $colors){
        $finalValue = "";
        $colors = array_unique($colors);
        //create string from array
        foreach ($colors as $key => $color){
            if ($key == 0){
                $finalValue = $color;
            }else{
                $finalValue = $finalValue.",".$color;
            }
        }
        return $finalValue;
    }
}