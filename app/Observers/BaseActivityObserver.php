<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

abstract class BaseActivityObserver
{
    /**
     * Label resource dalam Bahasa Indonesia (untuk deskripsi).
     * Contoh: return 'Aset';
     */
    abstract protected function label(): string;

    /**
     * Log name (grup log). Default 'siam'.
     */
    protected function logName(): string
    {
        return 'siam';
    }

    /**
     * Field yang di-skip saat log (mis. timestamps, password).
     */
    protected function except(): array
    {
        return ['created_at', 'updated_at'];
    }

    public function created(Model $model): void
    {
        $this->log($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->log($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted');
    }

    protected function log(Model $model, string $event): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();
        $label = $this->label();
        $except = $this->except();

        $old = [];
        $new = [];

        if ($event === 'created') {
            $new = collect($model->getAttributes())->except($except)->toArray();
            $desc = "{$user->name} menambah {$label}";
        } elseif ($event === 'updated') {
            $changes = collect($model->getChanges())->except($except)->toArray();
            if (empty($changes)) {
                return;
            }
            foreach ($changes as $key => $val) {
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $val;
            }
            $desc = "{$user->name} mengubah {$label}";
        } else {
            $old = collect($model->getAttributes())->except($except)->toArray();
            $desc = "{$user->name} menghapus {$label}";
        }

        activity($this->logName())
            ->causedBy($user)
            ->performedOn($model)
            ->event($event)
            ->withProperties([
                'old' => $old,
                'new' => $new,
                'model' => class_basename($model),
            ])
            ->log($desc);
    }
}
