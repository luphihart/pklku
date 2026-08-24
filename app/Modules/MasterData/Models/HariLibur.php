<?php

namespace App\Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'is_nasional',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_nasional' => 'boolean',
    ];

    /**
     * Check if a specific date is a registered holiday.
     */
    public static function isHoliday(string|Carbon $date): bool
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return static::where('tanggal_mulai', '<=', $dateStr)
            ->where('tanggal_selesai', '>=', $dateStr)
            ->exists();
    }

    /**
     * Get the holiday instance for a specific date if any.
     */
    public static function getHoliday(string|Carbon $date): ?static
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return static::where('tanggal_mulai', '<=', $dateStr)
            ->where('tanggal_selesai', '>=', $dateStr)
            ->first();
    }

    /**
     * Get all holiday dates as Y-m-d strings between two dates.
     */
    public static function getHolidayDatesBetween(string|Carbon $start, string|Carbon $end): array
    {
        $startStr = $start instanceof Carbon ? $start->toDateString() : Carbon::parse($start)->toDateString();
        $endStr = $end instanceof Carbon ? $end->toDateString() : Carbon::parse($end)->toDateString();

        $holidays = static::where(function ($q) use ($startStr, $endStr) {
            $q->whereBetween('tanggal_mulai', [$startStr, $endStr])
              ->orWhereBetween('tanggal_selesai', [$startStr, $endStr])
              ->orWhere(function ($sub) use ($startStr, $endStr) {
                  $sub->where('tanggal_mulai', '<=', $startStr)
                      ->where('tanggal_selesai', '>=', $endStr);
              });
        })->get();

        $holidayDates = [];
        $rangeStart = Carbon::parse($startStr);
        $rangeEnd = Carbon::parse($endStr);

        foreach ($holidays as $h) {
            $hStart = Carbon::parse($h->tanggal_mulai);
            $hEnd = Carbon::parse($h->tanggal_selesai);

            $curr = $hStart->greaterThan($rangeStart) ? $hStart->copy() : $rangeStart->copy();
            $limit = $hEnd->lessThan($rangeEnd) ? $hEnd->copy() : $rangeEnd->copy();

            while ($curr->lessThanOrEqualTo($limit)) {
                $holidayDates[] = $curr->toDateString();
                $curr->addDay();
            }
        }

        return array_values(array_unique($holidayDates));
    }
}
