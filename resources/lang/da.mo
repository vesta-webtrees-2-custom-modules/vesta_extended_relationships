��    e      D  �   l      �     �     �     �  )   �  "   		  "   ,	  \   O	  E   �	  �   �	  r   �
  p   )  :   �  '   �  B   �  e   @  6  �     �     �          $     6  !   G     i  	   |  �   �       v   !     �    �     �  0   �  _        |  2   �  M   �       "   9  $   \  F   �  )   �  w   �  q   j  .   �  4     )   @  8   j  W   �  y   �  �   u  �     �   �  n   V  d   �  V   *     �  B   �  �   �  ,   u     �  e   �  5     A   J     �  	   �  ?   �     �       W   "  \   z  2   �  {   
     �  2   �  #   �     �  4     �  8  _   �  u   %  0  �  h   �   '   5!  ?   ]!  '   �!  �   �!  �   �"  q   #  �   #  �   '$  t   �$  -   %%  1   S%  V  �%  T   �&  @   1'     r'     z'     �'  	   �'     �'  �  �'     �)     �)     �)  3   �)  +   *  &   =*  V   d*  F   �*  �   +  �   �+  c   R,  ]   �,  (   -  E   =-  c   �-  Q  �-     9/     N/  <   e/     �/     �/     �/     �/     �/  �   �/     �0  s   �0     �0    1     '2  3   F2  Y   z2  "   �2  :   �2  K   23  !   ~3  (   �3  *   �3  F   �3  (   ;4  �   d4  q   �4  ,   c5  4   �5  ,   �5  0   �5  b   #6  {   �6  �   7  z   �7  �   8  ~   �8  f   L9  a   �9  !   :  C   7:  �   {:  '   );     Q;  [   b;  5   �;  >   �;  '   3<     [<  h   d<     �<     �<  Z   �<  m   Z=  >   �=  q   >     y>  $   �>  (   �>     �>  C   �>  �  +?  W   �@  }   A    �A  R   �B  '   �B  N   C  1   [C  �   �C     3D  {   �D  �   /E  �   �E  p   [F  :   �F  =   G  ?  EG  P   �H  I   �H  	    I     *I     =I     XI     dI         3         H   "   d   2   )   V                          4       K   W   X       ,   6          8       +   c   $              e   ]   =              [   
   &   R       0             M      S   -   :                     Z            P   G   <   >   O   A       5          T   \   ;               Q   #   U          1   9   7          !      .   _   '   %          N   b   C      @       a          L   I       ?      *   E           `       B   	         (   F   Y   ^   D          J                    /         (see below for details). %1$s is %2$s of %3$s. (Number of relationships: %s) (that's overall almost as close as: %1$s) (that's overall as close as: %1$s) (that's overall closer than: %1$s) (that's overall not significantly closer than the closest relationship via common ancestors) A chart of an individual’s repeated ancestors, formatted as a tree. A module providing various algorithms used to determine relationships. Includes a chart displaying relationships between two individuals, as a replacement for the original 'Relationships' module. A module providing various algorithms used to determine relationships. Includes an extended 'Relationships' chart. All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: Allow persistent toggle (user may show/hide relationships) Also includes an extended '%1$s' block. And hopefully it shows how much better the new algorithm works ... Associated facts and events are displayed when the respective toggle checkbox is selected on the tab. Basically, each path (via common ancestors) between two individuals contributes to the CoR, inversely proportional to its length: Each path of length 2 (e.g. between full siblings) adds %1$s, each path of length 4 (e.g. between first cousins) adds %2$s, in general each path of length n adds (0.5)<sup>n</sup>. Chart Settings Closest Relationship: %s CoI; Coefficient of Inbreeding Common ancestor:  Common ancestors Common ancestors of %1$s and %2$s Common ancestors:  Debugging Determines the shortest path between two individuals via a LCA (lowest common ancestor), i.e. a common ancestor who only appears on the path once. Display Displays additional relationship information via the extended 'Families' tab, and the extended 'Facts and Events' tab. Do not show any relationship Each SLCA (smallest lowest common ancestor) essentially represents a part of the tree which both individuals share (as part of their ancestors). More technically, the SLCA set of two individuals is a subset of the LCA set (excluding all LCAs that are themselves ancestors of other LCAs). Families Tab Settings Find a closest relationship via common ancestors Find a closest relationship via common ancestors, or fallback to the closest overall connection Find all overall connections Find all relationships via lowest common ancestors Find all smallest lowest common ancestors, show a closest connection for each Find other overall connections Find other/all overall connections Find the closest overall connections Find the closest overall connections (preferably via common ancestors) Finished - all link dates are up-to-date. For close relationships similar to the previous option, but faster. Internally just a combination of two other methods. For large trees, this process may initially take several minutes. You can always safely abort and continue later. How to determine relationships between parents How to determine relationships to associated persons How to determine relationships to spouses How to determine relationships to the default individual If this is unexpected, and there are recent changes, you may have to follow this link:  If this option is checked, relationships to the associated individuals are only shown for the following facts and events. If you do not want to change the functionality with respect to the original Facts and Events tab, select 'Do not show any relationship' in the first block. If you do not want to change the functionality with respect to the original Families tab, select 'Do not show any relationship' everywhere. If you do not want to use the chart functionality, hide this chart via Control Panel > Charts > %1$s Vesta Extended Relationships (note that the chart is listed under the module name). If you select this option everywhere, you should also disallow persistent toggle, as it has no visible effect. In particular if both lists are empty, relationships will never be shown for these facts and events. In that case, you should also disallow persistent toggle, as it has no visible effect. Individuals with Patriarchs Intended as a replacement for the original 'Relationships' module. It is more complicated to determine this exact CoR, and the differences are usually small anyway. Therefore, only the Uncorrected CoR is calculated. Legacy algorithm for Relationship path names More Charts Note that the facts and events to be displayed at all may be filtered via the preferences of the tab. Only show relationships for specific facts and events Options referring to overall connections established before %1$s. Options to show in the chart Patriarch Patriarchs are the male end-of-line ancestors ('Spitzenahnen'). Pedigree collapse Pedigree collapse tree of %s Prefers partial paths via common ancestors, even if there is no direct common ancestor. Same option as in the original relationship chart, further configurable via recursion level: Same option as in the original relationship chart. Searching for regular overall connections would be pointless here because there is always a trivial HUSB - WIFE connection. Show common ancestors Show common ancestors on top of relationship paths Show legacy relationship path names Synchronization Synchronize trees to obtain dated relationship links The CoR (Coefficient of Relationship) is proportional to the number of genes that two individuals have in common as a result of their genetic relationship. Its calculation is based on Sewall Wright's method of path coefficients. Its value represents the proportion of genes held in common by two related individuals over and above those held in common by the whole population. More details here:  The following options refer to the same algorithms as used in the extended relationships chart: The following options use dated links, i.e. links that have been established before the date of the respective event. The process should be repeated (but will finish much faster) whenever a tree is edited, otherwise you may obtain inconsistent relationship data (displayed relationships are always valid, but they may not actually have been established at the given date, if changes in the tree are not synchronized here). The same information may be obtained via the branches list, where they show up as the heads of branches. There are no recorded common ancestors. There is no recorded pedigree collapse within %1$s generations. There is no recorded pedigree collapse. Therefore, if one of the following options is selected, overall connections are determined via dated links, i.e. links that have been established before the date of the respective event. These relationships may only be calculated efficiently by preprocessing the tree data, via the synchronization link at the top of this page. This allows you to present meaningful connections, such as 'widowed husband marries the sister of his dead wife'. This list provides an overview by surname, and may be used to determine whether all individuals with a specific surname are actually descended from a common patriarch. This process calculates dates for all INDI - FAM links, so that relationships at a specific point in time can be calculated efficiently. This seems more useful in most cases (e.g. to determine the relationship to a godparent at the time of the baptism). Uncorrected CoR (Coefficient of Relationship) Uncorrected CoR (Coefficient of Relationship): %s Under normal circumstances the proportion of genes transmitted from ancestor to descendant &ndash; as estimated by Σ(0.5)<sup>n</sup> &ndash;  and the proportion of genes they hold in common (the true coefficient of relationship) are the same. If there has been any inbreeding, however, there may be a slight discrepancy, as explained here:  You can disable this via the module preferences, it's mainly intended for debugging. You may also adjust the access level of this part of the module. parents show full pedigree show repeated ancestors once unlimited via legacy algorithm: %s Project-Id-Version: Danish (Vesta Webtrees Custom Modules)
Report-Msgid-Bugs-To: 
PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE
Last-Translator: FULL NAME <EMAIL@ADDRESS>
Language-Team: Danish <https://hosted.weblate.org/projects/vesta-webtrees-custom-modules/vesta-extended-relationships/da/>
Language: da
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=n != 1;
X-Generator: Weblate 5.10
  .(se detaljer nedenfor). %1$s er %3$ss %2$s. (Antal relationer: %s) (det er samlet set næsten lige så tæt som: %1$s) (det er samlet set lige så tæt som: %1$s) (det er samlet set tættere end: %1$s) (det er ikke væsentligt tættere end den tætteste relation baseret på fælles aner) Et diagram over en persons gentagne forfædre, formateret som et træ. Et modul, der tilbyder forskellige algoritmer til at bestemme slægtsforhold. Indeholder et diagram, der viser relationer mellem to personer, som en erstatning for det oprindelige 'Relationer'-modul. Et modul, der tilbyder forskellige algoritmer til at bestemme slægtsforhold. Indeholder en udvidet version af 'Relationer'-diagrammet. Alle stier mellem de to personer, der bidrager til CoR (relationskoefficienten), som defineret her: Aktivér vedvarende visningskontrol (brugeren kan skifte mellem at vise og skjule relationer) Inkluderer også en udvidet '%1$s'-blok. Og forhåbentlig viser det, hvor meget bedre den nye algoritme er ... Tilknyttede fakta og hændelser aktiveres, når den tilhørende afkrydsningsboks vælges på fanen. Hver sti mellem to personer (via fælles aner) bidrager til beslægthedsgaden (CoR), med en værdi, der er omvendt proportional med længden: En sti af længde 2 (f.eks. mellem helsøskende) tilføjer %1$s, en sti af længde 4 (f.eks. mellem første kusiner) tilføjer %2$s, og generelt tilføjer hver sti af længde n (0,5)<sup>n</sup>. Diagramindstillinger Nærmeste relation: %s CoI; Genetisk indavlskoefficient (Coefficient of Inbreeding) Fælles ane:  Fælles aner Fælles aner for %1$s og %2$s Fælles aner:  Fejlfinding Bestemmer den korteste sti mellem to personer via en LCA (laveste fælles ane), dvs. en fælles ane, der kun optræder én gang på stien. Vis Giver ekstra oplysninger om relationer via den udvidede 'Familier'-fane og den udvidede 'Fakta og hændelser'-fane. Vis ingen relationer Hver SLCA (mindste laveste fælles ane) repræsenterer grundlæggende en del af træet, som begge personer deler (som en del af deres aner). Mere teknisk er SLCA-sættet for to personer en delmængde af LCA-sættet, hvor alle LCA'er, der selv er aner til andre LCA'er, er udeladt. Indstillinger for familiefanen Find den nærmeste slægtsrelation via fælles aner Find den tætteste relation gennem fælles aner, ellers den korteste forbindelse generelt Find alle overordnede forbindelser Find alle relationer gennem den tætteste fælles forfader Find alle nærmeste fælles aner, og vis den tætteste forbindelse for hver Find andre generelle forbindelser Find andre/alle overordnede forbindelser Find de tætteste overordnede forbindelser Find de nærmeste familiebånd, primært baseret på fælles forfædre Færdig - alle linkdatoer er opdaterede. Til hurtigere identifikation af nære relationer, baseret på den tidligere metode. Implementeret som en intern kombination af to teknikker. For store træer kan denne proces i starten tage flere minutter. Du kan altid trygt afbryde og fortsætte senere. Sådan bestemmes relationer mellem forældre Sådan bestemmes relationer til tilknyttede personer Sådan bestemmes relationer til ægtefæller Sådan bestemmes relationer til standardpersonen Hvis dette ikke var forventet, og der har været ændringer for nylig, bør du følge dette link:  Hvis denne valgmulighed er markeret, vises relationer til de tilknyttede personer kun for de følgende fakta og hændelser. Hvis du ikke ønsker at ændre funktionaliteten i forhold til den oprindelige Fakta og Hændelser-fane, skal du vælge 'Vis ingen relationer' i den første blok. Hvis du ønsker at bibeholde den oprindelige funktion i Familier-fanen, vælg 'Vis ingen relationer' i alle indstillinger. Hvis du ikke vil benytte diagramfunktionen, kan du skjule diagrammet via Kontrolpanel > Diagrammer > %1$s Vesta Extended Relationships (diagrammet findes under modulnavnet). Hvis du vælger denne indstilling alle steder, bør du også slå vedvarende skift fra, da det ikke har nogen synlig virkning. I tilfælde af at begge lister er tomme, vil relationer ikke blive vist for disse fakta og hændelser. I dette tilfælde bør du også slå vedvarende skift fra, da det ikke har nogen synlig virkning. Individer med mandlige stamfædre Tiltænkt som en erstatning for det oprindelige 'Relationer'-modul. Det er en mere kompleks opgave at beregne den præcise beslægthedsgrad (CoR), og forskellene er som regel små. Derfor beregnes kun den ukorrigerede beslægthedsgrad (CoR). Ældre algoritme for relationssti-navne Flere diagrammer Bemærk, at hvilke fakta og hændelser der vises, kan styres via indstillingerne for fanen. Vis kun relationer for specifikke fakta og hændelser Indstillinger for overordnede forbindelser oprettet før %1$s. Valgmuligheder til visning i diagrammet Patriark Patriarker er de mandlige stamfædre, der repræsenterer slutningen af en slægtslinje ('Spitzenahnen'). Anetavlesammenfald Anetavlesammenfaldstræ for %s Foretrækker delvise stier via fælles aner, selv hvis der ikke er en direkte fælles ane. Samme valgmulighed som i det oprindelige relationsdiagram, med yderligere konfiguration via rekursionsniveau: Den samme valgmulighed som i det oprindelige relationsdiagram. Det giver ingen mening at lede efter generelle relationer her, da der altid er en åbenlys MAND-KONE forbindelse. Vis fælles aner Vis fælles aner over relationsstier Vis oprindelige navne på relationsstier Synkronisering Synkroniser træer for at få forbindelse med datoer for relationer Beslægthedsgraden (Coefficient of Relationship) er proportional med antallet af gener, som to individer har til fælles som følge af deres genetiske relation. Beregningen er baseret på Sewall Wrights metode med "path coefficients". Værdien repræsenterer andelen af gener, som to beslægtede personer har til fælles, ud over dem, der er fælles for hele befolkningen. Mere information her:  Følgende indstillinger bruger de samme algoritmer som i det udvidede relationsdiagram: Følgende indstillinger bestemmer relationer ud fra forbindelser, der blev etableret før datoen for den relevante hændelse. Husk at gentage processen (det vil gå meget hurtigere) hver gang træet redigeres, ellers kan der opstå inkonsistente data for relationer (de viste relationer er gyldige, men måske ikke opdateret til den rette dato, hvis træændringer ikke synkroniseres). De samme data kan hentes fra grene-listen, hvor de vises som spidserne af grenene. Der er ingen registrerede fælles aner. Der er ikke registreret nogen form for anetavlesammenfald i %1$s generationer. Der er ikke registreret noget anetavlesammenfald. Hvis en af de følgende indstillinger er valgt, findes overordnede relationer baseret på forbindelser, der var etableret før datoen for den pågældende hændelse. For at opnå en effektiv beregning af disse relationer skal trædataene først synkroniseres via linket øverst på denne side. Dette gør det muligt at præsentere meningsfulde relationer, såsom 'enkemand gifter sig med sin afdøde hustrus søster'. Denne liste giver et overblik efter efternavn og kan bruges til at afgøre, om alle personer med et bestemt efternavn faktisk stammer fra en fælles patriark. Denne proces fastsætter datoer for alle INDI - FAM-links, hvilket gør det muligt at beregne relationer på et bestemt tidspunkt effektivt. Dette giver bedre mening i de fleste situationer, såsom ved bestemmelse af relationen til en fadder ved dåben. Ukorrigeret beslægthedsgrad (Coefficient of Relationship) Ukorrigeret slægtskabsgrad (Coeffecient of Relationship): %s Under normale omstændigheder er andelen af gener, der overføres fra forfader til efterkommer – som estimeret af Σ(0,5)<sup>n</sup> – og andelen af gener, de har til fælles (den sande beslægthedsgrad), den samme. Hvis der har været nogen form for indavl, kan der dog være en lille forskel, som forklaret her:  Du kan slå dette fra via modulindstillingerne, det er primært til fejlfinding. Det er også muligt at tilpasse adgangsniveauet for denne del af modulet. forældre Vis hele anetavlen Vis gentagne aner én gang ubegrænset Via oprindelig algoritme: %s 