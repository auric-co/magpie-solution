<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

require './../vendor/autoload.php';

class Scrape
{
    private array $products = [];
    private string $url = "https://www.magpiehq.com/developer-challenge";
    private array $colors = [];
    public $helper;

    public function __construct()
    {
        $this->helper = new Helpers();
    }

    public function run(): void
    {
        ScrapeHelper::fetchDocument($this->url."/smartphones")->filter('#pages')->each(function (Crawler $pageLinks, $i){
            $pageLinks->filter('div[class="flex flex-wrap justify-center -mx-6"]')->each(function (Crawler $crawler, $i){
                $crawler->filter("a")->each(function (Crawler $link, $i){
                    $page = $this->url.substr($link->attr("href"),2);
                    ScrapeHelper::fetchDocument($page)->filter(".product")->each(function (Crawler $node, $i) {
                        $node->filter('span[class="border border-black rounded-full block"]')->each(function (Crawler $color){
                            $this->colors[] = $color->attr("data-colour");
                        });
                        $product = new Product();
                        $product->title = $node->filter(".product-name")->text();
                        $product->capacityMB = $this->helper->capacityFormat($node->filter(".product-capacity")->text());
                        $product->imageUrl = $this->url.substr($node->filter('img')->attr('src'), 2);
                        $product->colour = $this->colors;
                        $product->price = $node->filter('div[class="my-8 block text-center text-lg"]')->text();
                        $product->availabilityText = substr($node->filter('div[class="my-4 text-sm block text-center"]')->first()->text(),14);
                        $product->shippingDate = $this->helper->shippingDate($node->filter('div[class="my-4 text-sm block text-center"]')->first()->text(), $node->filter('div[class="my-4 text-sm block text-center"]')->last()->text());
                        $product->isAvailable = $this->helper->availabilityCheck($product->availabilityText);
                        $this->products[] = $product;
                    });
                });

            });

        });
        file_put_contents('output.json', json_encode($this->helper->uniqueProducts($this->products)));
        header('Content-Type: application/json');
        echo json_encode($this->helper->uniqueProducts($this->products));
    }
}

$scrape = new Scrape();
$scrape->run();
