<?php
namespace CakeTools\Traits;

trait SlugTrait
{

    protected function _setSlug($slug)
    {
        if ($slug) {
            $slug = mb_strtolower($slug);
            $this->set('slug_hash', md5($slug));
        }
        
        return $slug;
    }

    protected function _setSlugOld($slug)
    {
        if ($slug) {
            $slug = mb_strtolower($slug);
            $this->set('slug_old_hash', md5($slug));
        }

        return $slug;
    }
}