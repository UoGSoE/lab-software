<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Software>
 */
class SoftwareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $building = rand(1, 10) > 6 ? [fake()->randomElement(['Engineering', 'Physics', 'Maths', 'Chemistry', 'Geoscience', 'Computer Science'])] : null;
        $lab = $building ? (rand(1, 10) > 6 ? fake()->numberBetween(100, 500) : null) : null;
        return [
            'name' => $this->getSoftwareName(),
            'version' => fake()->randomElement(['1.0', '2.0', '3.0', '4.0', '5.0', '6.0', '7.0', '8.0', '9.0', '10.0']),
            'os' => [fake()->randomElement(['Windows', 'Windows', 'Windows', 'Windows', 'Mac', 'Linux', 'BSD'])],
            'building' => $building,
            'lab' => $lab,
            'config' => rand(1, 10) == 9 ? fake()->sentence() : null,
            'notes' => rand(1, 10) == 9 ? fake()->sentence() : null,
            'created_by' => User::factory(),
            'academic_session_id' => AcademicSession::factory(),
            'is_new' => false,
            'is_free' => false,
        ];
    }

    protected function getSoftwareName(): string
    {
        return fake()->randomElement([
            '.Net',
            'Office365',
            'MatLab',
            'R Project',
            'R Studio',
            'Read & Write',
            '7Zip',
            'Adobe Air',
            'Adobe Digital Editions',
            'Adobe Reader DC',
            'Anaconda 3',
            'Audacity with Lame',
            'Endnote',
            'FileZilla',
            'Gantt Project',
            'Ghostscript',
            'Gimp',
            'Google Chrome Enterprise',
            'GSView',
            'Inkscape',
            'IrfanView',
            'Java',
            'MikTex',
            'MindGenius',
            'Minitab 21',
            'Notepad++',
            'PDF24 Creator',
            'Putty',
            'SPSS',
            'TeXnicCenter',
            'Visual Studio Code',
            'VLC',
            'Wolfram Mathemiatica',
            'AutoCAD',
            'AutoDesk Revit',
            'HEC-RAS',
            'GIT',
            'MobaXTerm',
            'Chemdraw',
            'Gaussian',
            'Mnova',
            'Olex2',
            'Spartan',
            'Arduino IDE',
            'Blender',
            'Go',
            'Godot Engine',
            'Oculus Desktop',
            'MiniZinc',
            'MySQL Workbench',
            'NodeJS',
            'OpenJDK',
            'PgAdmin4',
            'Prism',
            'Spin',
            'Unity',
            'VirtualBox',
            'Vagrant',
            'Abaqus',
            'Antenna Magus/CST Studio',
            'Atena2D',
            'Elmer',
            'Epanet',
            'Firefox ESR',
            'HEC HMS',
            'LEGO Mindstorms EV3 Classroom',
            'LimitState Geo',
            'Mastan',
            'OpenLCA and database',
            'Plaxis 3D',
            'QuantumATK',
            'RobotC',
            'Rsoft Lasermod',
            'StarCCM+',
            'Tanner Tools',
            'TeraTerm',
            'WinSCP',
            'Electronics',
            'Lumerical',
            'Optic studio',
            'Structures',
            'Advanced Design Systems',
            'Ansys Structures',
            'ANSYS Fluids',
            'Autodesk Fusion 360',
            'Autodesk Inventor',
            'Autodesk Robot/Robot Structural Analysis',
            'AWR',
            'Comsol',
            'Portunus',
            'QuTip',
            'Simpleware',
            'LaserMOD',
            'OrCAD Gold',
            'Unreal Engine',
            'Pipeflow',
            'TecQuipment',
            'ArcGIS Pro',
            'Cmake',
            'GCD',
            'GMT',
            'BaseCamp',
            'Google Earth Pro',
            'GSHHG',
            'ImageJ',
            'Leica Infinisy',
            'MultiSpecWin',
            'Orfeo Toolbox',
            'Paraview',
            'Petroleum Experts Move',
            'PHREEQC',
            'Pix4D Mapper',
            'QGIS',
            'Qt',
            'Revit',
            'River Surveyor',
            'Sedlog',
            'Snap Desktop',
            'StarNet',
            'Stereonet',
            'TauDEM',
            'Trimble Business Centre',
            'XMING',
            'Maple',
            'Quarto',
            'Gwyddion',
            'OpenNX',
            'Tracker',
            'X2Go',
        ]);
    }
}
