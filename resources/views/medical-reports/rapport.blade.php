<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport médical — {{ trim($patient->prenom.' '.$patient->nom) }}</title>
    <style>
        @page { margin: 16mm 15mm 22mm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Serif", "Times New Roman", serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1a1a1a;
        }
        .lh { width: 100%; border-bottom: 1px solid #333; padding-bottom: 6px; margin-bottom: 4px; }
        .lh td { vertical-align: middle; }
        .lh .logo { width: 60px; text-align: center; }
        .lh .org { text-align: center; }
        .lh .org .name { font-weight: bold; font-size: 15px; }
        .lh .org .slogan { font-style: italic; font-size: 11px; }
        h1.doc {
            text-align: center; font-size: 14.5px; font-weight: bold;
            text-decoration: underline; margin: 20px 0 6px; letter-spacing: .5px;
        }
        .type { text-align: center; font-size: 11px; margin: 0 0 14px; }
        .type .fill { font-weight: bold; }
        .fill { font-weight: bold; }
        .line { margin: 5px 0; }
        .lab { }
        .val { border-bottom: 1px dotted #999; }
        .ind { padding-left: 26px; }
        .ind2 { padding-left: 44px; }
        table.phys { width: 100%; border-collapse: collapse; margin: 5px 0; }
        table.phys td { padding: 2px 6px 2px 0; }
        .concl { margin-top: 8px; }
        .concl .box {
            border: 1px solid #333; padding: 8px 10px; min-height: 54px;
            margin-top: 3px; white-space: pre-wrap;
        }
        .foot { margin-top: 22px; width: 100%; }
        .foot td { vertical-align: top; padding-top: 8px; }
        .foot .sign { text-align: right; }
        .legal {
            position: fixed; bottom: -12mm; left: 0; right: 0;
            border-top: 1px solid #333; padding-top: 5px;
            text-align: center; font-size: 8px; line-height: 1.4; color: #444;
        }
        .dots { color: #888; }
    </style>
</head>
<body>
@php
    $moisFr = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];
    $dateFr = function ($valeur) use ($moisFr) {
        if (empty($valeur)) {
            return '—';
        }
        $d = \Illuminate\Support\Carbon::parse($valeur);
        $jour = $d->day === 1 ? '1er' : (string) $d->day;

        return $jour.' '.$moisFr[(int) $d->month].' '.$d->year;
    };
    $v = fn ($x) => filled($x) ? e($x) : '<span class="dots">&hellip;</span>';
    $legal = $centre['legal'] ?? [];
    $lieuEtab = $centre['lieu_etablissement'] ?? 'Douala';
@endphp

    <table class="lh">
        <tr>
            <td class="logo">
                @if (!empty($centre['logo_path']) && file_exists(public_path($centre['logo_path'])))
                    <img src="{{ public_path($centre['logo_path']) }}" style="max-width:54px;max-height:54px;">
                @else
                    <span style="font-family:'DejaVu Sans',sans-serif;font-size:26px;">&#9877;</span>
                @endif
            </td>
            <td class="org">
                <div class="name">{{ $centre['raison_sociale'] ?? 'Centre Médical' }}</div>
                <div class="slogan">«&nbsp;{{ $centre['slogan'] ?? '' }}&nbsp;»</div>
            </td>
            <td class="logo">
                @if (!empty($centre['logo_path']) && file_exists(public_path($centre['logo_path'])))
                    <img src="{{ public_path($centre['logo_path']) }}" style="max-width:54px;max-height:54px;">
                @else
                    <span style="font-family:'DejaVu Sans',sans-serif;font-size:26px;">&#9877;</span>
                @endif
            </td>
        </tr>
    </table>

    <h1 class="doc">RAPPORT MÉDICAL</h1>
    <p class="type">Type de visite : <span class="fill">{{ $typeVisiteLabel }}</span></p>

    <div class="line">
        Concernant Mr/Mme/Mlle
        <span class="fill">{{ trim($patient->prenom.' '.$patient->nom) }}</span>
    </div>
    <div class="line">
        Né(e) le <span class="fill">{{ $dateFr($patient->date_naissance) }}</span>
        &nbsp;&nbsp;&nbsp; Employé à : <span class="fill">{!! $v($visit->employeur) !!}</span>
        &nbsp;&nbsp;&nbsp; Poste : <span class="fill">{!! $v($visit->poste) !!}</span>
    </div>
    <div class="line">
        Téléphone : <span class="fill">{!! $v($patient->telephone) !!}</span>
    </div>

    <div class="line">Antécédents :</div>
    <div class="line ind">- Familiaux : <span class="fill">{!! $v($r->antecedents_familiaux) !!}</span></div>
    <div class="line ind">- Personnels : <span class="fill">{!! $v($r->antecedents_personnels) !!}</span></div>

    <div class="line" style="margin-top:10px;font-weight:bold;">Examens physiques</div>
    <table class="phys">
        <tr>
            <td>Poids : <span class="fill">{!! $v($r->poids) !!}</span></td>
            <td>Taille : <span class="fill">{!! $v($r->taille) !!}</span></td>
            <td>TA : <span class="fill">{!! $v($r->tension_arterielle) !!}</span></td>
            <td>FC : <span class="fill">{!! $v($r->frequence_cardiaque) !!}</span></td>
        </tr>
        @if (filled($r->autres_constantes))
        <tr><td colspan="4">Autres constantes : <span class="fill">{{ $r->autres_constantes }}</span></td></tr>
        @endif
        <tr>
            <td colspan="2">AVSC&nbsp;&nbsp; OD : <span class="fill">{!! $v($r->avsc_od) !!}</span>
                &nbsp; OG : <span class="fill">{!! $v($r->avsc_og) !!}</span>
                &nbsp; ODG : <span class="fill">{!! $v($r->avsc_odg) !!}</span></td>
            <td colspan="2">AVAC&nbsp;&nbsp; OD : <span class="fill">{!! $v($r->avac_od) !!}</span>
                &nbsp; OG : <span class="fill">{!! $v($r->avac_og) !!}</span>
                &nbsp; ODG : <span class="fill">{!! $v($r->avac_odg) !!}</span></td>
        </tr>
    </table>

    <div class="line">État général : <span class="fill">{!! $v($r->clinique_etat_general) !!}</span></div>
    <div class="line">Tête et cou : <span class="fill">{!! $v($r->clinique_tete_cou) !!}</span></div>
    <div class="line">Thorax :</div>
    <div class="line ind">- Poumons : <span class="fill">{!! $v($r->clinique_poumons) !!}</span></div>
    <div class="line ind">- Cœur : <span class="fill">{!! $v($r->clinique_coeur) !!}</span></div>
    <div class="line">Abdomen : <span class="fill">{!! $v($r->clinique_abdomen) !!}</span></div>
    <div class="line">Membres : <span class="fill">{!! $v($r->clinique_membres) !!}</span></div>
    <div class="line ind">Autres : <span class="fill">{!! $v($r->clinique_autres) !!}</span></div>

    <div class="line" style="margin-top:8px;">Biologie :
        &nbsp; GLY : <span class="fill">{!! $v($r->bio_glycemie) !!}</span>
        &nbsp; BU : <span class="fill">{!! $v($r->bio_bu) !!}</span>
        &nbsp; Hbsag : <span class="fill">{!! $v($r->bio_hbsag) !!}</span>
        &nbsp; SM : <span class="fill">{!! $v($r->bio_sm) !!}</span>
    </div>
    <div class="line ind">- Autres : <span class="fill">{!! $v($r->bio_autres) !!}</span></div>

    <div class="line" style="margin-top:8px;">Imageries :</div>
    <div class="line ind">- Radio thorax : <span class="fill">{!! $v($r->img_radio_thorax) !!}</span></div>
    <div class="line ind">- Autres : <span class="fill">{!! $v($r->img_autres) !!}</span></div>

    <div class="line" style="margin-top:8px;">Examens spéciaux : <span class="fill">{!! $v($r->examens_speciaux) !!}</span></div>

    <div class="concl">
        <div style="font-weight:bold;">Conclusion :</div>
        <div class="box">{{ $r->conclusion }}</div>
    </div>

    <table class="foot">
        <tr>
            <td>{{ $lieuEtab }}, le <span class="fill">{{ $dateFr($r->validated_at ?? $visit->date_visite) }}</span></td>
            <td class="sign">Le médecin<br><span class="fill">{{ $medecinNom }}</span></td>
        </tr>
    </table>

    <div class="legal">
        {{ $legal['forme_capital'] ?? '' }} — {{ $legal['siege'] ?? '' }}<br>
        RCCM : {{ $legal['rccm'] ?? '' }} — Contribuable N° : {{ $legal['contribuable'] ?? '' }} — Régime {{ $legal['regime'] ?? '' }}<br>
        Tél : {{ $legal['tel'] ?? '' }} — E-mail : {{ $legal['email'] ?? '' }}
    </div>
</body>
</html>
