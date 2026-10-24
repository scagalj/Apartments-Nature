<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Apartment;
use App\Models\Room;
use App\Models\Beds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingRequestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use App\Support\SpamGuard;

/**
 * Description of BedController
 *
 * @author Stjepan
 */
class BookingRequestController extends BaseController {

    public function sendInformationRequest(Request $request) {
        if ($reason = SpamGuard::check($request)) {
            return $this->blocked($reason, '#contact-form');
        }

        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:100', SpamGuard::NO_LINKS],
            'email' => ['required', 'email:rfc', 'max:150'],
            'note' => ['required', 'string', 'max:3000'],
        ], SpamGuard::messages());

        $collection = collect([
            'checkIn' => null,
            'checkOut' => null,
            'firstName' => $data['firstName'],
            'email' => $data['email'],
            'note' => $data['note'],
            'apartmentName' => '',
            'request' => 'informationRequest',
        ]);

        $this->send($collection, $data);
        session()->flash('booking-request-status', 'send');
        return redirect()->to(url()->previous() . '#contact-form');
    }

    public function sendBookingRequest(Request $request, $apartment_id) {
        $apartment = Apartment::findOrFail($apartment_id);

        if ($reason = SpamGuard::check($request)) {
            return $this->blocked($reason, '#booking');
        }

        $data = $request->validate([
            'checkIn' => ['required', 'date_format:d.m.Y', 'after_or_equal:today'],
            'checkOut' => ['required', 'date_format:d.m.Y', 'after:checkIn'],
            'guests' => ['required', 'integer', 'min:1', 'max:' . max(1, $apartment->getNumberOfGuests())],
            'firstName' => ['required', 'string', 'max:100', SpamGuard::NO_LINKS],
            'email' => ['required', 'email:rfc', 'max:150'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], SpamGuard::messages());

        // Never trust a price sent by the browser.
        $price = $apartment->calculatePrice($data['checkIn'], $data['checkOut'], $data['guests']);

        $collection = collect([
            'checkIn' => $data['checkIn'],
            'checkOut' => $data['checkOut'],
            'firstName' => $data['firstName'],
            'guests' => $data['guests'],
            'price' => $price > 0 ? '€ ' . number_format($price, 2) : 'ON REQUEST (no price set for these dates)',
            'email' => $data['email'],
            'note' => $data['note'] ?? '',
            'apartmentName' => $apartment->name,
            'request' => 'bookingRequest',
        ]);

        $this->send($collection, $data);
        session()->flash('booking-request-status', 'send');
        return redirect()->to(url()->previous() . '#booking');
    }

    private function send($collection, array $data) {
        $mail = (new BookingRequestMail($collection))
                ->replyTo($data['email'], SpamGuard::singleLine($data['firstName'], 100));
        Mail::to(config('site.booking_email'))->send($mail);
    }

    /**
     * Bots get the normal success message (so they do not retry with changes);
     * real visitors whose form expired or failed the captcha get an error.
     */
    private function blocked($reason, $anchor) {
        if (SpamGuard::isSilent($reason)) {
            session()->flash('booking-request-status', 'send');
            return redirect()->to(url()->previous() . $anchor);
        }
        $message = $reason === 'expired' ? __('site.form_expired') : __('site.form_captcha');
        return redirect()->to(url()->previous() . $anchor)->withErrors(['form' => $message])->withInput();
    }

}
