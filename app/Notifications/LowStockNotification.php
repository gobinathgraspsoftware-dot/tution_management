<?php

namespace App\Notifications;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $inventory;
    protected $alertType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Inventory $inventory, string $alertType = 'low_stock')
    {
        $this->inventory = $inventory;
        $this->alertType = $alertType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->alertType === 'out_of_stock'
            ? "[URGENT] Out of Stock Alert: {$this->inventory->name}"
            : "Low Stock Alert: {$this->inventory->name}";

        $level = $this->alertType === 'out_of_stock' ? 'error' : 'warning';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->alertType === 'out_of_stock') {
            $message->line("**URGENT:** The following inventory item is now out of stock:")
                ->line("**Item:** {$this->inventory->name}")
                ->line("**SKU:** {$this->inventory->sku}")
                ->line("**Category:** " . ($this->inventory->category->name ?? 'N/A'))
                ->line("**Current Stock:** 0")
                ->line("**Reorder Level:** {$this->inventory->reorder_level}")
                ->line('')
                ->line('Please restock this item immediately to avoid service disruption.');
        } else {
            $message->line("The following inventory item is running low on stock:")
                ->line("**Item:** {$this->inventory->name}")
                ->line("**SKU:** {$this->inventory->sku}")
                ->line("**Category:** " . ($this->inventory->category->name ?? 'N/A'))
                ->line("**Current Stock:** {$this->inventory->current_stock}")
                ->line("**Reorder Level:** {$this->inventory->reorder_level}")
                ->line('')
                ->line('Please consider restocking this item soon.');
        }

        $message->action('View Inventory', route('admin.inventory.show', $this->inventory))
            ->line('Thank you for your attention to this matter.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->alertType,
            'inventory_id' => $this->inventory->id,
            'inventory_name' => $this->inventory->name,
            'inventory_sku' => $this->inventory->sku,
            'category_name' => $this->inventory->category->name ?? 'N/A',
            'current_stock' => $this->inventory->current_stock,
            'reorder_level' => $this->inventory->reorder_level,
            'message' => $this->alertType === 'out_of_stock'
                ? "URGENT: {$this->inventory->name} is out of stock!"
                : "{$this->inventory->name} is running low on stock ({$this->inventory->current_stock} remaining)",
            'url' => route('admin.inventory.show', $this->inventory),
        ];
    }
}
