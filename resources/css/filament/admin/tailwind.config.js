import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './modules/filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/awcodes/overlook/resources/**/*.blade.php',
        './vendor/awcodes/filament-versions/resources/**/*.blade.php',
        './vendor/bezhansalleh/filament-exceptions/resources/views/**/*.blade.php',
    ],
}
