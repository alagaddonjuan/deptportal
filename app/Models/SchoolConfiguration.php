<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolConfiguration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'school_configurations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_name',
        'school_address',
        'school_phone',
        'school_email',
        'current_academic_year',
        'current_term_semester',
        'school_logo_path',
        'date_format',
        'app_timezone',
        'currency_symbol',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // No specific casts needed for these string/text fields by default,
        // unless you have specific formatting needs on retrieval/storage.
    ];

    /**
     * Get the currently active school configuration.
     * Since we expect only one row (or the first one as the active one).
     *
     * @return SchoolConfiguration|null
     */
    public static function current(): ?SchoolConfiguration
    {
        // Fetches the first record, or creates a default one if none exists.
        // This ensures there's always a record to interact with.
        return self::firstOrCreate(
            ['id' => 1], // Assuming we always use ID 1 for the primary/single config record
            [ // Default values if creating for the first time
                'school_name' => 'My School Portal',
                'app_timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'currency_symbol' => '$',
                // Add other sensible defaults if desired
            ]
        );
    }
}