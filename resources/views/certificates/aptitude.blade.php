<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Certificat d'aptitude {{ $reference }}</title>
    <style>
        @page { margin: 28mm 22mm; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #1a1a1a;
        }
        .entete { text-align: center; margin-bottom: 32px; }
        .entete h1 {
            font-size: 18px;
            letter-spacing: 1px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .entete .sous-titre { font-size: 11px; color: #555; }
        .corps p { margin: 0 0 14px; text-align: justify; }
        .decision {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 24px 0;
            padding: 12px;
            border: 1px solid #1a1a1a;
        }
        .mention { margin: 0 0 10px; }
        .validite { margin-top: 18px; font-weight: bold; }
        .signature {
            margin-top: 56px;
            width: 100%;
        }
        .signature td { vertical-align: top; font-size: 12px; }
        .signature .droite { text-align: right; }
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

        return $d->day.' '.$moisFr[(int) $d->month].' '.$d->year;
    };
@endphp

    <div class="entete">
        <h1>Certificat d'aptitude médicale</h1>
        <div class="sous-titre">Réf. {{ $reference }} — Centre Médical</div>
    </div>

    <div class="corps">
        <p>
            Je soussigné(e), <strong>Dr {{ $c->medecin_nom }}</strong>, médecin du travail,
            certifie avoir examiné ce jour :
        </p>

        <p>
            <strong>{{ $c->patient_prenom }} {{ $c->patient_nom }}</strong>,
            né(e) le {{ $dateFr($c->patient_date_naissance) }},
            au poste de <strong>{{ $c->poste ?? '—' }}</strong>,
            à l'occasion d'une <strong>{{ mb_strtolower($c->type_visite_label) }}</strong>.
        </p>

        <div class="decision">{{ mb_strtoupper($c->decision_label) }}</div>

        @if (! empty($c->restriction))
            <p class="mention"><strong>Restriction :</strong> {{ $c->restriction }}</p>
        @endif

        @if (! empty($c->recommandations))
            <p class="mention"><strong>Recommandations :</strong> {{ $c->recommandations }}</p>
        @endif

        <p class="validite">
            <strong>Validité :</strong> {{ $c->duree_validite_mois }} mois —
            expire le {{ $dateFr($c->date_expiration) }}.
        </p>
    </div>

    <table class="signature">
        <tr>
            <td>Fait à {{ $c->lieu }}, le {{ $dateFr($genere_le) }}</td>
            <td class="droite">Signature et cachet</td>
        </tr>
    </table>
</body>
</html>
