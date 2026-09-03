<?php

/*
|--------------------------------------------------------------------------
| Identité du Centre Médical (charte documentaire unique)
|--------------------------------------------------------------------------
|
| Alimente l'en-tête et le pied de page des documents produits (certificat
| d'aptitude, rapport médical). Un seul jeu de valeurs pour tout le centre —
| pas de modèle par entreprise. Surchargeable via le fichier .env.
|
*/

return [
    'raison_sociale' => env('CENTRE_RAISON_SOCIALE', 'CM Médecine du Travail SAS'),
    'slogan' => env('CENTRE_SLOGAN', 'Santé au travail et productivité assurée !!'),

    // « … en service au Centre Médical et médecine du travail de {lieu}. »
    'lieu_medecine_travail' => env('CENTRE_LIEU', 'Bali'),

    // Lieu par défaut d'établissement du certificat (« Fait à … »).
    'lieu_etablissement' => env('CENTRE_LIEU_ETABLISSEMENT', 'Douala'),

    // Chemin (relatif à public/) d'un logo optionnel pour l'en-tête. Vide = pas de logo.
    'logo_path' => env('CENTRE_LOGO_PATH', ''),

    // Mentions légales du pied de page.
    'legal' => [
        'forme_capital' => env('CENTRE_LEGAL_CAPITAL', 'Centre Médical Médecine du travail SAS au Capital Social de 980 000 FCFA'),
        'siege' => env('CENTRE_LEGAL_SIEGE', 'Siège social : Douala-Cameroun au quartier Bali'),
        'rccm' => env('CENTRE_LEGAL_RCCM', 'CM-DLA-01-B16-00006'),
        'contribuable' => env('CENTRE_LEGAL_CONTRIBUABLE', 'M053416771386S'),
        'regime' => env('CENTRE_LEGAL_REGIME', 'SIMPLIFIÉ'),
        'tel' => env('CENTRE_LEGAL_TEL', '+237 695 193 035 / 651 581 823'),
        'email' => env('CENTRE_LEGAL_EMAIL', 'commtbali@gmail.com'),
    ],
];
