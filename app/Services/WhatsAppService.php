<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a simulated WhatsApp message.
     */
    public function sendMessage($phone, $message)
    {
        // In a real scenario, you'd use Twilio, UltraMsg, or a similar API.
        // For this professional demo, we'll log it and simulate a successful response.

        Log::info("WhatsApp Message sent to {$phone}: {$message}");

        // Simulate API latency
        // usleep(500000); 

        return [
            'success' => true,
            'message_id' => 'WA-' . bin2hex(random_bytes(8)),
            'status' => 'sent_simulated'
        ];
    }
}
