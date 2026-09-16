<?php

namespace App\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JsonSerializable;
use Traversable;

class AuditLog extends Model
{
    protected $fillable = ['actor_id','action','entity_type','entity_id','auditable_type','auditable_id','meta_json','ip','user_agent'];
    protected function casts(): array { return ['meta_json'=>'array']; }
    public function actor(): BelongsTo { return $this->belongsTo(User::class,'actor_id'); }

    public function displayAction(): string
    {
        return $this->stringifyForDisplay($this->action);
    }

    public function displayActor(): string
    {
        return $this->stringifyForDisplay($this->actor?->name);
    }

    public function displayEntity(): string
    {
        $type = $this->stringifyForDisplay($this->entity_type ?? $this->auditable_type);
        $id = $this->stringifyForDisplay($this->entity_id ?? $this->auditable_id);

        if ($type === '-' && $id === '-') {
            return '-';
        }

        if ($id === '-') {
            return $type;
        }

        return "{$type}#{$id}";
    }

    public function displayMeta(): string
    {
        return $this->stringifyForDisplay($this->meta_json);
    }

    private function stringifyForDisplay(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            $string = trim((string) $value);

            return $string !== '' ? $string : '-';
        }

        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json !== false && $json !== 'null' ? $json : '-';
    }
}
