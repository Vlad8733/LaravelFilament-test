<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Страница "О нас"
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Страница "Контакты"
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Отправка формы контактов
     */
    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        // Здесь можно отправить email или сохранить в базу
        // Mail::to('admin@example.com')->send(new ContactMessage($validated));

        return back()->with('success', __('pages.contact_success'));
    }

    /**
     * Страница FAQ
     */
    public function faq()
    {
        return view('pages.faq');
    }

    /**
     * Страница "Недавно просмотренные"
     */
    public function recentlyViewed()
    {
        return view('pages.recently-viewed');
    }

    /**
     * Privacy Policy page
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Terms of Service page
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Cookie Policy page
     */
    public function cookies()
    {
        return view('pages.cookies');
    }
}
