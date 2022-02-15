��    `        �         (     )     C     Y  )   w  "   �  "   �  \   �  �   D	  r   
  p   {
  :   �
  B   '  e   j  6  �               %     7  	   J  �   T     �  v   �     f    �     �  0   �  _   �     J  2   g  M   �     �  "     $   *  F   O  )   �  w   �  q   8  .   �  4   �  )     8   8  W   q  y   �  �   C  �   �  �   k  n   $  d   �  V   �     O  B   k  �   �  ,   C     p  e   �  5   �  A   "     d  	   �  ?   �  W   �     #     6     G  #   U  \   y  2   �  Q   	  {   [     �  2   �  #         D     U  4   e  �  �  _   '  u   �  0  �  h   .   �   �   �   R!  q   �!  �   Q"  �   �"  t   �#  -   �#  1   %$  V  W$  T   �%  @   &     D&  	   L&     V&     o&  �  t&  &   A(     h(     (  :   �(  4   �(  /   )  }   <)  �   �)  �   �*  l   "+  R   �+  F   �+  �   ),  x  �,     ,.     9.     V.     u.     �.  �   �.  	   Z/  �   d/     �/  J  0  "   Z1  H   }1  l   �1     32  L   O2  d   �2     3  "   3  *   B3  [   m3  .   �3  �   �3  �   �4  ,   5  7   35  .   k5  2   �5  ]   �5  �   +6  �   �6  �   j7  �   8  w   �8  l   [9  _   �9     (:  H   @:  �   �:  .   H;     w;  �   �;  D   <  M   ]<  /   �<  	   �<  U   �<  �   ;=     �=     �=     �=  #   �=  j   >  ?   �>  c   �>  y   ,?  "   �?  ;   �?  %   @     +@     >@  M   M@  �  �@     ;B  �   �B  a  JC  p   �D  �   E  �   �E  m   �F  �   �F  �   �G  �   GH  *   �H  .   �H  g  $I  `   �J  C   �J     1K  	   8K     BK  	   ^K     U   L           E       #      R          7   5   (                 P   X          <   %   2   "           ?   B           ]       +       [          )   V                 *   S   9   _   C          I      T      4   \   J   
              ,                     @   M      $   Y   -                /   H          3       !   .   F   Q           W      8   G   '   :       ;       D   N      1   K                  O   6   	              =          >          ^   &         `   A   0      Z     (see below for details). %1$s is %2$s of %3$s. (Number of relationships: %s) (that's overall almost as close as: %1$s) (that's overall as close as: %1$s) (that's overall closer than: %1$s) (that's overall not significantly closer than the closest relationship via common ancestors) A module providing various algorithms used to determine relationships. Includes a chart displaying relationships between two individuals, as a replacement for the original 'Relationships' module. A module providing various algorithms used to determine relationships. Includes an extended 'Relationships' chart. All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: Allow persistent toggle (user may show/hide relationships) And hopefully it shows how much better the new algorithm works ... Associated facts and events are displayed when the respective toggle checkbox is selected on the tab. Basically, each path (via common ancestors) between two individuals contributes to the CoR, inversely proportional to its length: Each path of length 2 (e.g. between full siblings) adds %1$s, each path of length 4 (e.g. between first cousins) adds %2$s, in general each path of length n adds (0.5)<sup>n</sup>. Calculating… Chart Settings Common ancestor:  Common ancestors:  Debugging Determines the shortest path between two individuals via a LCA (lowest common ancestor), i.e. a common ancestor who only appears on the path once. Display Displays additional relationship information via the extended 'Families' tab, and the extended 'Facts and Events' tab. Do not show any relationship Each SLCA (smallest lowest common ancestor) essentially represents a part of the tree which both individuals share (as part of their ancestors). More technically, the SLCA set of two individuals is a subset of the LCA set (excluding all LCAs that are themselves ancestors of other LCAs). Families Tab Settings Find a closest relationship via common ancestors Find a closest relationship via common ancestors, or fallback to the closest overall connection Find all overall connections Find all relationships via lowest common ancestors Find all smallest lowest common ancestors, show a closest connection for each Find other overall connections Find other/all overall connections Find the closest overall connections Find the closest overall connections (preferably via common ancestors) Finished - all link dates are up-to-date. For close relationships similar to the previous option, but faster. Internally just a combination of two other methods. For large trees, this process may initially take several minutes. You can always safely abort and continue later. How to determine relationships between parents How to determine relationships to associated persons How to determine relationships to spouses How to determine relationships to the default individual If this is unexpected, and there are recent changes, you may have to follow this link:  If this option is checked, relationships to the associated individuals are only shown for the following facts and events. If you do not want to change the functionality with respect to the original Facts and Events tab, select 'Do not show any relationship' in the first block. If you do not want to change the functionality with respect to the original Families tab, select 'Do not show any relationship' everywhere. If you do not want to use the chart functionality, hide this chart via Control Panel > Charts > %1$s Vesta Extended Relationships (note that the chart is listed under the module name). If you select this option everywhere, you should also disallow persistent toggle, as it has no visible effect. In particular if both lists are empty, relationships will never be shown for these facts and events. In that case, you should also disallow persistent toggle, as it has no visible effect. Individuals with Patriarchs Intended as a replacement for the original 'Relationships' module. It is more complicated to determine this exact CoR, and the differences are usually small anyway. Therefore, only the Uncorrected CoR is calculated. Legacy algorithm for Relationship path names No relationship found Note that the facts and events to be displayed at all may be filtered via the preferences of the tab. Only show relationships for specific facts and events Options referring to overall connections established before %1$s. Options to show in the chart Patriarch Patriarchs are the male end-of-line ancestors ('Spitzenahnen'). Prefers partial paths via common ancestors, even if there is no direct common ancestor. Relationship to me Relationship: %s Relationships Relationships between %1$s and %2$s Same option as in the original relationship chart, further configurable via recursion level: Same option as in the original relationship chart. Searching for all possible relationships can take a lot of time in complex trees. Searching for regular overall connections would be pointless here because there is always a trivial HUSB - WIFE connection. Show common ancestors Show common ancestors on top of relationship paths Show legacy relationship path names Swap individuals Synchronization Synchronize trees to obtain dated relationship links The CoR (Coefficient of Relationship) is proportional to the number of genes that two individuals have in common as a result of their genetic relationship. Its calculation is based on Sewall Wright's method of path coefficients. Its value represents the proportion of genes held in common by two related individuals over and above those held in common by the whole population. More details here:  The following options refer to the same algorithms as used in the extended relationships chart: The following options use dated links, i.e. links that have been established before the date of the respective event. The process should be repeated (but will finish much faster) whenever a tree is edited, otherwise you may obtain inconsistent relationship data (displayed relationships are always valid, but they may not actually have been established at the given date, if changes in the tree are not synchronized here). The same information may be obtained via the branches list, where they show up as the heads of branches. Therefore, if one of the following options is selected, overall connections are determined via dated links, i.e. links that have been established before the date of the respective event. These relationships may only be calculated efficiently by preprocessing the tree data, via the synchronization link at the top of this page. This allows you to present meaningful connections, such as 'widowed husband marries the sister of his dead wife'. This list provides an overview by surname, and may be used to determine whether all individuals with a specific surname are actually descended from a common patriarch. This process calculates dates for all INDI - FAM links, so that relationships at a specific point in time can be calculated efficiently. This seems more useful in most cases (e.g. to determine the relationship to a godparent at the time of the baptism). Uncorrected CoR (Coefficient of Relationship) Uncorrected CoR (Coefficient of Relationship): %s Under normal circumstances the proportion of genes transmitted from ancestor to descendant &ndash; as estimated by Σ(0.5)<sup>n</sup> &ndash;  and the proportion of genes they hold in common (the true coefficient of relationship) are the same. If there has been any inbreeding, however, there may be a slight discrepancy, as explained here:  You can disable this via the module preferences, it's mainly intended for debugging. You may also adjust the access level of this part of the module. parents unlimited via legacy algorithm: %s view Project-Id-Version: Dutch (Vesta Webtrees Custom Modules)
Report-Msgid-Bugs-To: 
PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE
Last-Translator: FULL NAME <EMAIL@ADDRESS>
Language-Team: Dutch <https://hosted.weblate.org/projects/vesta-webtrees-custom-modules/vesta-extended-relationships/nl/>
Language: nl
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=n != 1;
X-Generator: Weblate 4.11-dev
  (zie hieronder voor meer informatie). %1$s is %2$s van %3$s. (Aantal verwantschappen: %s) (dat is over het algemeen bijna net zo dichtbij als: %1$s) (dat is over het algemeen net zo dichtbij als: %1$s) (dat is over het algemeen dichterbij dan: %1$s) (dat is over het algemeen niet significant dichterbij dan de dichtstbijzijnde verwantschap via gemeenschappelijke voorouders) Een module die verschillende algoritmen biedt die worden gebruikt om verwantschappen te bepalen. Bevat een diagram met verwantschappen tussen twee personen, als vervanging voor de oorspronkelijke module 'Verwantschappen'. Een module die verschillende algoritmen biedt die worden gebruikt om verwantschappen te bepalen. Bevat een uitgebreide 'Verwantschappen'. Alle paden tussen de twee personen die bijdragen aan de verwantschapscoëfficiënt, zoals hier gedefinieerd: Permanente schakeling toestaan (gebruiker kan verwantschappen weergeven/verbergen) En hopelijk laat het zien hoeveel beter het nieuwe algoritme werkt ... Feiten en gebeurtenissen met gerelateerde personen worden getoond wanneer het desbetreffende schakelvakje is ingeschakeld op het tabblad. Elk pad (via gemeenschappelijke voorouders) tussen twee personen draagt bij aan de verwantschapscoëfficiënt, omgekeerd evenredig met de lengte: Elk pad van lengte 2 (bijvoorbeeld tussen volle broers en zussen) voegt %1$s toe, elk pad van lengte 4 (bijvoorbeeld tussen eerste neven en nichten) voegt %2$s toe, in het algemeen voegt elk pad van lengte n (0,5)<sup>n</sup> toe. Berekenen… Instellingen voor diagrammen Gemeenschappelijke voorouder:  Gemeenschappelijke voorouders:  Foutopsporing Bepaalt het kortste pad tussen twee personen via een LCA (laagste gemeenschappelijke voorouder), d.w.z. een gemeenschappelijke voorouder die slechts één keer op het pad verschijnt. Weergeven Geeft aanvullende verwantschapsinformatie weer via het uitgebreide tabblad 'Gezinnen' en het uitgebreide tabblad 'Feiten en gebeurtenissen'. Toon geen enkele verwantschap Elke SLCA (kleinste dichtstbijzijnde gemeenschappelijke voorouder) vertegenwoordigt in wezen een deel van de stamboom die beide personen delen (als onderdeel van hun voorouders). Meer technisch, de SLCA-set van twee personen is een subset van de LCA set (met uitzondering van alle LCA's die zelf voorouders zijn van andere LCA's). Instellingen voor tabblad Gezinnen Zoek een dichtstbijzijnde verwantschap via gemeenschappelijke voorouders Zoek een dichtstbijzijnde verwantschap via gemeenschappelijke voorouders, of anders dichtstbijzijnde relatie Zoek alle algemene relaties Zoek alle verwantschappen via dichtstbijzijnde gemeenschappelijke voorouders Zoek alle dichtstbijzijnde gemeenschappelijke voorouders, toon een dichtstbijzijnde relatie voor elk Zoek andere algemene relaties Zoek andere/alle algemene relaties Zoek de dichtstbijzijnde algemene relaties Zoek de dichtstbijzijnde algemene relaties (bij voorkeur via gemeenschappelijke voorouders) Klaar - alle koppelingsdatums zijn up-to-date. Voor nauwe verwantschappen die vergelijkbaar zijn met de vorige optie, maar sneller. Intern gewoon een combinatie van twee andere methoden. Voor grote stambomen kan dit proces in eerste instantie enkele minuten duren. U kunt altijd veilig afbreken en later verder gaan. Hoe verwantschappen tussen ouders te bepalen Hoe verwantschappen met gerelateerd personen te bepalen Hoe verwantschappen met echtgenoten te bepalen Hoe verwantschappen met de startpersoon te bepalen Als dit onverwacht is en er recente wijzigingen zijn, moet u mogelijk deze koppeling volgen:  Als deze optie is ingeschakeld, worden verwantschappen met gerelateerde personen alleen getoond voor de volgende feiten en gebeurtenissen. Als u de functionaliteit ten opzichte van het oorspronkelijke tabblad Feiten en gebeurtenissen niet wilt wijzigen, selecteert u 'Toon geen enkele verwantschap' in het eerste blok. Als u de functionaliteit ten opzichte van het oorspronkelijke tabblad Gezinnen niet wilt wijzigen, selecteert u overal 'Toon geen enkele verwantschap'. Als u de diagramfunctionaliteit niet wilt gebruiken, verbergt u deze diagram via Configuratiescherm > Modules > Diagrammen > %1$s Vesta Uitgebreide verwantschappen (merk op dat het diagram wordt getoond onder de modulenaam). Als u deze optie overal selecteert, moet u ook permanente schakeling verbieden, omdat deze geen zichtbaar effect heeft. Als beide lijsten leeg zijn, zullen verwantschappen nooit worden getoond voor deze feiten en gebeurtenissen. In dat geval moet u ook permanente schakeling verbieden, omdat het geen zichtbaar effect heeft. Personen met stamvaders Bedoeld als vervanging voor de oorspronkelijke module 'Verwantschappen'. Het is ingewikkelder om deze exacte verwantschapscoëfficiënt bepalen, en de verschillen zijn meestal toch klein. Daarom wordt alleen de ongecorrigeerde verwantschapscoëfficiënt berekend. Verouderd algoritme voor verwantschapspadnamen Geen verwantschap gevonden Merk op dat de feiten en gebeurtenissen die wel moeten worden weergegeven, kunnen worden gefilterd via de voorkeuren van het tabblad. Toon alleen verwantschappen voor specifieke feiten en gebeurtenissen Opties die verwijzen naar algemene relaties die zijn vastgesteld vóór %1$s. Opties die in het diagram moeten worden getoond Stamvader Patriarchen zijn de mannelijke voorouders aan het einde van de lijn ('Spitzenahnen'). Geeft de voorkeur aan gedeeltelijke paden via gemeenschappelijke voorouders, zelfs als er geen directe gemeenschappelijke voorouder is. Verwantschap met mij Verwantschap: %s Verwantschappen Verwantschappen tussen %1$s en %2$s Dezelfde optie als in het oorspronkelijke verwantschapsdiagram, verder configureerbaar via recursieniveau: Dezelfde optie als in het oorspronkelijke verwantschapsdiagram. Het zoeken naar alle mogelijke verwantschappen kan veel tijd in beslag nemen in complexe stambomen. Zoeken naar gebruikelijke algemene relaties zou hier zinloos zijn omdat er altijd een triviale HUSB - WIFE verbinding is. Toon gemeenschappelijke voorouders Toon gemeenschappelijke voorouders boven verwantschapspaden Toon verouderde verwantschapspadnamen Verwissel personen Synchronisatie Stambomen synchroniseren om gedateerde verwantschapskoppelingen te verkrijgen De verwantschapscoëfficiënt is evenredig aan het aantal genen dat twee personen gemeen hebben als gevolg van hun genetische verwantschap. De berekening is gebaseerd op Sewall Wright's methode van padcoëfficiënten. De waarde ervan vertegenwoordigt het aandeel van de genen die gemeenschappelijk zijn voor twee verwante individuen boven die welke door de hele bevolking gemeenschappelijk zijn. Meer details hier:  De volgende opties hebben betrekking op dezelfde algoritmen als die worden gebruikt in het diagram uitgebreide verwantschappen: De volgende opties maken gebruik van gedateerde links, d.w.z. koppelingen die vastgesteld zijn vóór de datum van de betreffende gebeurtenis. Het proces moet worden herhaald (maar zal veel sneller klaar zijn) wanneer een stamboom wordt bewerkt, anders kunt u inconsistente verwantschapsgegevens verkrijgen (weergegeven verwantschappen zijn altijd geldig, maar ze zijn mogelijk niet daadwerkelijk vastgesteld op de gegeven datum, als wijzigingen in de stamboom hier niet worden gesynchroniseerd). Dezelfde informatie kan worden verkregen via de lijst met takken, waar ze verschijnen als de hoofden van takken. Daarom, als een van de volgende opties is geselecteerd, worden relaties dus bepaald via gedateerde koppelingen, d.w.z. koppelingen die vastgesteld zijn vóór de datum van de betreffende gebeurtenis. Deze verwantschappen kunnen alleen efficiënt worden berekend door de stamboomgegevens voor te bewerken, via de synchronisatiekoppeling boven aan deze pagina. Zo kunt u betekenisvolle verbanden presenteren, zoals 'weduwnaar trouwt met de zus van zijn overleden vrouw'. Deze lijst geeft een overzicht per achternaam en kan worden gebruikt om te bepalen of alle personen met een bepaalde achternaam daadwerkelijk afstammen van een gemeenschappelijke patriarch. Dit proces berekent datums voor alle INDI - FAM-koppelingen, zodat verwantschappen op een bepaald moment in de tijd efficiënt kunnen worden berekend. Dit lijkt in de meeste gevallen nuttiger (bijvoorbeeld om de verwantschap met een peetouder te bepalen op het moment van de doop). Ongecorrigeerde verwantschapscoëfficiënt Ongecorrigeerde verwantschapscoëfficiënt: %s Onder normale omstandigheden is het percentage genen dat wordt overgedragen van voorouder naar afstammeling &ndash; zoals geschat door Σ(0.5)<sup>n</sup> &ndash; gelijk aan het aandeel genen dat ze gemeen hebben (de echte verwantschapscoëfficiënt). Als er echter sprake is geweest van inteelt, kan er een lichte afwijking zijn, zoals hier wordt uitgelegd:  U kunt dit uitschakelen via de modulevoorkeuren, het is voornamelijk bedoeld voor foutopsporing. U kunt ook het toegangsniveau van dit deel van de module aanpassen. ouders onbeperkt via verouderd algoritme: %s weergeven 