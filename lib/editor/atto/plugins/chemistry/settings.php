<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings that allow configuration of the list of tex examples in the chemistry editor.
 *
 * @package    atto_chemistry
 * @copyright  2014 Geoffrey Rowland <rowland.geoff@gmail.com>
 * Based on    @package atto_equation
 * @copyright  2013 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_chemistry', new lang_string('pluginname', 'atto_chemistry')));

$settings = new admin_settingpage('atto_chemistry_settings', new lang_string('settings', 'atto_chemistry'));
if ($ADMIN->fulltree) {
    // Group 1.
    $name = new lang_string('librarygroup1', 'atto_chemistry');
    $desc = new lang_string('librarygroup1_desc', 'atto_chemistry');
    $default = '
X+
X^2+
Y^3+
X-
X^2-
X^3-
X2
X2Y
X2Y3
{}^{a}X
{}^{a}_{b}X
{}^{a}_{b}X^{c}
$\overset{\ce{a}}{\ce{X}}$
$\underset{\ce{b}}{\ce{X}}$
$\overset{\ce{a}}{\underset{\ce{b}}{\ce{X}}}$
$\pm$
+
$\oplus$
$\ominus$
-
e-
I
II
III
IV
V
VI
VII
VIII
IX
X
(s)
(l)
(g)
(aq)
';
    $setting = new admin_setting_configtextarea('atto_chemistry/librarygroup1',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

    // Group 2.
    $name = new lang_string('librarygroup2', 'atto_chemistry');
    $desc = new lang_string('librarygroup2_desc', 'atto_chemistry');
    $default = '
.
:
$.$
$\circ$
{\:}^{$\circ$}C
{\:}^{$\circ$}F
$+$
$-$
$=$
$\oplus$
$\ominus$
1/2
$\frac{[\ce{A}]}{[\ce{B}]}$
$\frac{[\ce{A}][\ce{B}]}{[\ce{C}]}$
$\frac{[\ce{A}][\ce{B}]}{[\ce{C}][\ce{D}]}$
${K}$
${K}_\ce{c}$
${K}_\ce{a}$
${K}_\ce{w}$
${K}_\ce{stab}$
$\Delta H$
$\Delta H_\ce{r}$
$\Delta H_\ce{c}$
$\Delta H_\ce{f}$
$\Delta H_\ce{r}^\ominus$
$\Delta H_\ce{c}^\ominus$
$\Delta H_\ce{f}^\ominus$
$\Delta G$
${T}$
$\Delta S$
${E}^{\ominus}$
${A}_\ce{r}$
${M}_\ce{r}$
pH
p${K}$_{a}
$\log$
$\log_{10}$
mol
mol^{-1}
dm^3
dm^{-3}
$]\:[$
$]\quad[$
';
    $setting = new admin_setting_configtextarea('atto_chemistry/librarygroup2',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

    // Group 3.
    $name = new lang_string('librarygroup3', 'atto_chemistry');
    $desc = new lang_string('librarygroup3_desc', 'atto_chemistry');
    $default = '
-
=
#
\bond{~}
\bond{~-}
\bond{~=}
\bond{-~-}
\bond{...}
\bond{....}
\bond{->}
\bond{<-}
->
<-
<->
<=>
<=>>
<<=>
->[\ce{above}]
->[\ce{above}][\ce{below}]
->[][\ce{below}]
<=>[\ce{above}]
<=>[\ce{above}][\ce{below}]
<=>[][\ce{below}]
^
v
$\upharpoonleft$
$\downharpoonright$
';
    $setting = new admin_setting_configtextarea('atto_chemistry/librarygroup3',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

    // Group 4.
    $name = new lang_string('librarygroup4', 'atto_chemistry');
    $desc = new lang_string('librarygroup4_desc', 'atto_chemistry');
    $default = '
$\alpha$
$\beta$
$\gamma$
$\delta$
$\epsilon$
$\zeta$
$\eta$
$\theta$
$\iota$
$\kappa$
$\lambda$
$\mu$
$\nu$
$\xi$
${o}$
$\pi$
$\rho$
$\sigma$
$\tau$
$\upsilon$
$\phi$
$\chi$
$\psi$
$\omega$
A
B
$\Gamma$
$\Delta$
E
Z
H
$\Theta$
I
K
$\Lambda$
M
N
$\Xi$
O
$\Pi$
P
$\Sigma$
T
$\Upsilon$
$\Phi$
X
$\Psi$
$\Omega$
';
    $setting = new admin_setting_configtextarea('atto_chemistry/librarygroup4',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

    // Group 5.
    $name = new lang_string('librarygroup5', 'atto_chemistry');
    $desc = new lang_string('librarygroup5_desc', 'atto_chemistry');
    $default = '
${a}$
${b}$
${c}$
${d}$
${e}$
${f}$
${g}$
${h}$
${i}$
${j}$
${k}$
${l}$
${m}$
${n}$
${o}$
${p}$
${q}$
${r}$
${s}$
${t}$
${u}$
${v}$
${w}$
${x}$
${y}$
${z}$
${A}$
${B}$
${C}$
${D}$
${E}$
${F}$
${G}$
${H}$
${I}$
${J}$
${K}$
${L}$
${M}$
${N}$
${O}$
${P}$
${Q}$
${R}$
${S}$
${T}$
${U}$
${V}$
${W}$
${X}$
${Y}$
${Z}$
';
    $setting = new admin_setting_configtextarea('atto_chemistry/librarygroup5',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

    // Group 6.
    $name = new lang_string('librarygroup6', 'atto_chemistry');
    $desc = new lang_string('librarygroup6_desc', 'atto_chemistry');
    $default = '
1
2
3
4
5
6
7
8
9
10
11
12
13
14
15
16
17
18
H
.
.
.
.
.
.
.
.
.
.
.
.
.
.
.
.
He
Li
Be
.
.
.
.
.
.
.
.
.
.
B
C
N
O
F
Ne
Na
Mg
.
.
.
.
.
.
.
.
.
.
Al
Si
P
S
Cl
Ar
K
Ca
Sc
Ti
V
Cr
Mn
Fe
Co
Ni
Cu
Zn
Ga
Ge
As
Se
Br
Kr
Rb
Sr
Y
Zr
Nb
Mo
Tc
Ru
Rh
Pd
Ag
Cd
In
Sn
Sb
Te
I
Xe
Cs
Ba
$\dagger$
Hf
Ta
W
Re
Os
Ir
Pt
Au
Hg
Tl
Pb
Bi
Po
At
Rn
Fr
Ra
$\ddagger$
Rf
Db
Sg
Bh
Hs
Mt
Ds
Rg
Cn

';
    $setting = new admin_setting_configtextarea('atto_chemistry/librarygroup6',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

    // Group 7.
    $name = new lang_string('librarygroup7', 'atto_chemistry');
    $desc = new lang_string('librarygroup7_desc', 'atto_chemistry');
    $default = '
$\dagger$
La
Ce
Pr
Nd
Pm
Sm
Eu
Gd
Tb
Dy
Ho
Er
Tm
Yb
Lu
.
.
$\ddagger$
Ac
Th
Pa
U
Np
Pu
Am
Cm
Bk
Cf
Es
Fm
Md
No
Lr
';
    $setting = new admin_setting_configtextarea('atto_chemistry/librarygroup7',
                                                $name,
                                                $desc,
                                                $default);
    $settings->add($setting);

}
