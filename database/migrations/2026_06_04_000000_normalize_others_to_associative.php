<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class NormalizeOthersToAssociative extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Process permits in chunks to avoid memory issues
        DB::table('permits')->orderBy('id')->chunk(100, function ($permits) {
            foreach ($permits as $p) {
                try {
                    $updates = [];

                    // --- Normalize ppe_other ---
                    $rawPpeOther = $p->ppe_other;
                    $ppe = $p->ppe;
                    // Decode ppe to array if needed
                    $ppeArr = is_array($ppe) ? $ppe : (@json_decode($ppe, true) ?: []);

                    // Find Lainnya slugs from ppe entries (entries like 'Lainnya_slug' expected)
                    $lainnyaSlugs = [];
                    if (is_array($ppeArr)) {
                        foreach ($ppeArr as $entry) {
                            if (is_string($entry) && str_starts_with($entry, 'Lainnya_')) {
                                $lainnyaSlugs[] = substr($entry, strlen('Lainnya_'));
                            }
                        }
                    }

                    $newPpeOther = [];
                    if (is_null($rawPpeOther) || $rawPpeOther === '') {
                        $newPpeOther = [];
                    } elseif (is_string($rawPpeOther)) {
                        $decoded = @json_decode($rawPpeOther, true);
                        if (is_array($decoded)) {
                            // If associative, keep as-is; if numeric, try to map to slugs
                            if (array_values($decoded) === $decoded) {
                                // numeric array
                                if (!empty($lainnyaSlugs)) {
                                    foreach ($decoded as $i => $val) {
                                        $key = $lainnyaSlugs[$i] ?? ('other_' . ($i+1));
                                        $newPpeOther[$key] = $val;
                                    }
                                } else {
                                    // map to generic keys
                                    foreach ($decoded as $i => $val) {
                                        $newPpeOther['other_' . ($i+1)] = $val;
                                    }
                                }
                            } else {
                                $newPpeOther = $decoded;
                            }
                        } else {
                            // plain string -> map to first slug if exists, else 'other'
                            $key = $lainnyaSlugs[0] ?? 'other';
                            $newPpeOther[$key] = $rawPpeOther;
                        }
                    } elseif (is_array($rawPpeOther)) {
                        // if associative keep, if numeric try map
                        if (array_values($rawPpeOther) === $rawPpeOther) {
                            if (!empty($lainnyaSlugs)) {
                                foreach ($rawPpeOther as $i => $val) {
                                    $key = $lainnyaSlugs[$i] ?? ('other_' . ($i+1));
                                    $newPpeOther[$key] = $val;
                                }
                            } else {
                                foreach ($rawPpeOther as $i => $val) {
                                    $newPpeOther['other_' . ($i+1)] = $val;
                                }
                            }
                        } else {
                            $newPpeOther = $rawPpeOther;
                        }
                    }

                    // --- Normalize safety_checklists_other ---
                    $rawSafetyOther = $p->safety_checklists_other;
                    $newSafetyOther = [];
                    if (is_null($rawSafetyOther) || $rawSafetyOther === '') {
                        $newSafetyOther = [];
                    } elseif (is_string($rawSafetyOther)) {
                        $decoded = @json_decode($rawSafetyOther, true);
                        if (is_array($decoded)) {
                            if (array_values($decoded) === $decoded) {
                                foreach ($decoded as $i => $val) {
                                    $newSafetyOther['other_' . ($i+1)] = $val;
                                }
                            } else {
                                $newSafetyOther = $decoded;
                            }
                        } else {
                            $newSafetyOther['other'] = $rawSafetyOther;
                        }
                    } elseif (is_array($rawSafetyOther)) {
                        if (array_values($rawSafetyOther) === $rawSafetyOther) {
                            foreach ($rawSafetyOther as $i => $val) {
                                $newSafetyOther['other_' . ($i+1)] = $val;
                            }
                        } else {
                            $newSafetyOther = $rawSafetyOther;
                        }
                    }

                    // Only update if changes detected (compare json)
                    $curPpeOtherJson = @json_encode(@json_decode($p->ppe_other, true) ?: []);
                    $newPpeOtherJson = json_encode($newPpeOther ?: new stdClass());
                    $curSafetyJson = @json_encode(@json_decode($p->safety_checklists_other, true) ?: []);
                    $newSafetyJson = json_encode($newSafetyOther ?: new stdClass());

                    $toUpdate = [];
                    // store as JSON objects/arrays consistent with model casting (array)
                    $toUpdate['ppe_other'] = $newPpeOther;
                    $toUpdate['safety_checklists_other'] = $newSafetyOther;

                    DB::table('permits')->where('id', $p->id)->update($toUpdate);
                } catch (\Throwable $e) {
                    // continue on error, but don't stop whole migration
                    continue;
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: revert would require application-specific heuristics. Leave data as associative arrays.
    }
}
