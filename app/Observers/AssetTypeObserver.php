<?php

namespace App\Observers;

use App\Models\AssetType;

class AssetTypeObserver
{
    public function created(AssetType $assetType): void
    {
        \Log::info('AssetTypeObserver::created fired');
        $this->log($assetType, 'created');
    }

    public function updated(AssetType $assetType): void
    {
        \Log::info('AssetTypeObserver::updated fired');
        $this->log($assetType, 'updated');
    }

    public function deleted(AssetType $assetType): void
    {
        \Log::info('AssetTypeObserver::deleted fired');
        $this->log($assetType, 'deleted');
    }

    protected function log(AssetType $model, string $event): void
    {
        if (!auth()->check())
            return;

        $user = auth()->user();
        $old = [];
        $new = [];

        if ($event === 'created') {
            $new = $model->getAttributes();
            unset($new['created_at'], $new['updated_at']);
            $desc = "{$user->name} menambah Brand & Model";
        } elseif ($event === 'updated') {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes))
                return;
            foreach ($changes as $key => $val) {
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $val;
            }
            $desc = "{$user->name} mengubah Brand & Model";
        } else {
            $old = $model->getAttributes();
            unset($old['created_at'], $old['updated_at']);
            $desc = "{$user->name} menghapus Brand & Model";
        }

        activity('siam')
            ->causedBy($user)
            ->performedOn($model)
            ->event($event)
            ->withProperties(['old' => $old, 'new' => $new, 'model' => 'AssetType'])
            ->log($desc);
    }
}
