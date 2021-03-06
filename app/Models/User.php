<?php

namespace App\Models;

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\HasConnectedAccounts;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\Models\Activity;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto {
        getProfilePhotoUrlAttribute as getPhotoUrl;
    }
    use HasTeams;
    use HasConnectedAccounts;
    use Notifiable;
    use Searchable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name).'&color=FFFFFF&background=000000&size=512';
    }

    /**
     * Get the QR code SVG of the user's two factor authentication QR code URL.
     *
     * @return string
     */
    public function twoFactorQrCodeSvg()
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(new Alpha(0, new Rgb(0, 0, 0)), new Rgb(113, 113, 122))),
                new SvgImageBackEnd
            )
        ))->writeString($this->twoFactorQrCodeUrl());

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if (filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)) {
            return $this->profile_photo_path;
        }

        return $this->getPhotoUrl();
    }

    /**
     * Get the activity associated with this user.
     */
    public function activity()
    {
        $method = ['created' => 'Created', 'updated' => 'Updated'];
        $type = ['App\Models\Pen' => 'pen'];

        return Activity::inLog('pens')
            ->causedBy($this)
            ->latest('updated_at')
            ->when($this->id !== optional(request()->user())->id,
                fn ($query) => $query->whereHas('subject',
                    fn ($query) => $query->where('visibility', 'public')
                )
            )
            ->with('subject:id,title,slug,visibility')
            ->paginate(5)
            ->map(function ($activity) use ($method, $type) {
                return [
                    'id' => $activity->id,
                    'method' => $method[$activity->description],
                    'type' => $type[$activity->subject_type],
                    'subject' => $activity->subject,
                    'at' => str_replace(' ago', '', $activity->updated_at->shortRelativeToNowDiffForHumans()),
                ];
            });
    }

    /**
     * Get the pens that this user owns.
     */
    public function pens()
    {
        return $this->hasMany(Pen::class);
    }

    /**
     * Generates a random, unique username
     */
    public static function generateUsername()
    {
        $username = Str::random(20);
        $check = User::where('username', $username)->first();

        if ($check) {
            return static::generateUsername();
        }

        return $username;
    }
}
