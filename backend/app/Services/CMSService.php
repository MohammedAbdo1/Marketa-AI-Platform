<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageContent;
use App\Models\Testimonial;
use App\Models\Faq;
use Illuminate\Support\Facades\Cache;

class CMSService extends BaseService
{
    /**
     * Get a page with all its sections and content
     */
    public function getPage(string $slug, string $locale = 'ar')
    {
        $cacheKey = "page_{$slug}_{$locale}";
        
        return Cache::remember($cacheKey, 3600, function () use ($slug) {
            return Page::with(['sections' => function ($query) {
                $query->where('is_active', true)
                      ->with(['content' => function ($q) {
                          $q->where('is_active', true);
                      }]);
            }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
        });
    }

    /**
     * Update a page section
     */
    public function updateSection(int $id, array $data)
    {
        $section = PageSection::findOrFail($id);
        $section->update($data);
        
        // Clear cache
        Cache::forget("page_{$section->page->slug}_ar");
        Cache::forget("page_{$section->page->slug}_en");
        
        return $section->fresh('content');
    }

    /**
     * Update page content
     */
    public function updateContent(int $id, array $data)
    {
        $content = PageContent::findOrFail($id);
        $content->update($data);
        
        // Clear cache
        $page = $content->section->page;
        Cache::forget("page_{$page->slug}_ar");
        Cache::forget("page_{$page->slug}_en");
        
        return $content;
    }

    /**
     * Get all sections
     */
    public function getSections()
    {
        return PageSection::with('page', 'content')
            ->orderBy('page_id')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all active testimonials
     */
    public function getTestimonials(bool $featuredOnly = false)
    {
        $query = Testimonial::where('is_active', true);
        
        if ($featuredOnly) {
            $query->where('is_featured', true);
        }
        
        return $query->orderBy('sort_order')->get();
    }

    /**
     * Get all FAQs grouped by category
     */
    public function getFaqs(string $locale = 'ar')
    {
        return Faq::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy("category_{$locale}");
    }

    /**
     * Create a new page section
     */
    public function createSection(array $data)
    {
        $section = PageSection::create($data);
        
        // Clear cache
        Cache::forget("page_{$section->page->slug}_ar");
        Cache::forget("page_{$section->page->slug}_en");
        
        return $section;
    }

    /**
     * Create page content
     */
    public function createContent(array $data)
    {
        $content = PageContent::create($data);
        
        // Clear cache
        $page = $content->section->page;
        Cache::forget("page_{$page->slug}_ar");
        Cache::forget("page_{$page->slug}_en");
        
        return $content;
    }

    /**
     * Delete a section
     */
    public function deleteSection(int $id)
    {
        $section = PageSection::findOrFail($id);
        $pageSlug = $section->page->slug;
        
        $section->delete();
        
        // Clear cache
        Cache::forget("page_{$pageSlug}_ar");
        Cache::forget("page_{$pageSlug}_en");
        
        return true;
    }

    /**
     * Delete content
     */
    public function deleteContent(int $id)
    {
        $content = PageContent::findOrFail($id);
        $page = $content->section->page;
        
        $content->delete();
        
        // Clear cache
        Cache::forget("page_{$page->slug}_ar");
        Cache::forget("page_{$page->slug}_en");
        
        return true;
    }
}

