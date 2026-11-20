<?php
namespace Core\Helpers;

use Cocur\Slugify\Slugify;

class Pkg
{
    public function slug(string $str)
    {
        $slugify = new Slugify();
        return $slugify->slugify($str);
    }
}