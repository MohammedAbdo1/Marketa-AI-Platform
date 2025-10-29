<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\Faq;
use App\Models\Plan;
use App\Services\CMSService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    protected CMSService $cmsService;

    public function __construct(CMSService $cmsService)
    {
        $this->cmsService = $cmsService;
    }

    /**
     * Get a page by slug with all sections and content
     */
    public function getPage($slug, Request $request)
    {
        try {
            $locale = $request->get('locale', 'ar');
            $page = $this->cmsService->getPage($slug, $locale);

            return response()->json([
                'success' => true,
                'data' => $page,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }
    }

    /**
     * Get all testimonials
     */
    public function getTestimonials(Request $request)
    {
        $featuredOnly = $request->boolean('featured', false);
        $testimonials = $this->cmsService->getTestimonials($featuredOnly);

        return response()->json([
            'success' => true,
            'data' => $testimonials,
        ]);
    }

    /**
     * Get all FAQs
     */
    public function getFaqs(Request $request)
    {
        $locale = $request->get('locale', 'ar');
        $faqs = $this->cmsService->getFaqs($locale);

        return response()->json([
            'success' => true,
            'data' => $faqs,
        ]);
    }

    /**
     * Get all active plans
     */
    public function getPlans(Request $request)
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }
}
