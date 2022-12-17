��    W      �     �      �     �     �     �  )   �  "     "   $  \   G  �   �  r   h	  p   �	  :   L
  B   �
  e   �
  6  0     g     v     �  	   �  �   �     8  v   @     �    �     �  0   
  _   ;     �  2   �  M   �     9  "   X  $   {  F   �  )   �  w     q   �  .   �  4   *  )   _  8   �  W   �  y     �   �  �   0  �   �  n   u  d   �  V   I     �  B   �  �   �  ,   �  e   �  5   '  A   ]     �  	   �  ?   �  W     \   ^  2   �  {   �     j  2   �  #   �     �  4   �  �    _   �  u   	  0    h   �  �     �   �  q   a   �   �   �   {!  t   "  -   y"  1   �"  V  �"  T   0$  @   �$     �$  	   �$     �$  �  �$  #   �&     '     '  2   1'  +   d'  )   �'  Q   �'  �   (  y   �(  a   6)  <   �)  I   �)  d   *  +  �*     �+     �+     �+     �+  �   �+     },  m   �,     �,  �   -       .  +   !.  c   M.      �.  ,   �.  I   �.      I/      j/  )   �/  A   �/  *   �/  {   "0  x   �0  *   1  4   B1  *   w1  0   �1  Z   �1  �   .2  �   �2  �   T3  �   �3  s   �4  X   5  X   j5     �5  @   �5  �   6  :   �6  x   �6  3   U7  I   �7  (   �7     �7  /   8  X   C8  k   �8  <   9  �   E9     �9  -   �9  1   :     9:  7   H:  k  �:  g   �;  �   T<    �<  a   �=  �   J>  �   ?  �   �?  �   @  �   �@  t   >A  )   �A  -   �A  C  B  U   OC  @   �C     �C  
   �C     �C        <           F       I      E   @                     ?   %   &           R   P              C      .   0   '   ;   J           $   +       U      =   4              2   5             O          Q                         1   #      *       	   7             ,          3               V              8                  
              D   S   A         K   B          N      "   T   L   W      -   (   G             :      >   9           H   M           /   )   6   !         (see below for details). %1$s is %2$s of %3$s. (Number of relationships: %s) (that's overall almost as close as: %1$s) (that's overall as close as: %1$s) (that's overall closer than: %1$s) (that's overall not significantly closer than the closest relationship via common ancestors) A module providing various algorithms used to determine relationships. Includes a chart displaying relationships between two individuals, as a replacement for the original 'Relationships' module. A module providing various algorithms used to determine relationships. Includes an extended 'Relationships' chart. All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: Allow persistent toggle (user may show/hide relationships) And hopefully it shows how much better the new algorithm works ... Associated facts and events are displayed when the respective toggle checkbox is selected on the tab. Basically, each path (via common ancestors) between two individuals contributes to the CoR, inversely proportional to its length: Each path of length 2 (e.g. between full siblings) adds %1$s, each path of length 4 (e.g. between first cousins) adds %2$s, in general each path of length n adds (0.5)<sup>n</sup>. Chart Settings Common ancestor:  Common ancestors:  Debugging Determines the shortest path between two individuals via a LCA (lowest common ancestor), i.e. a common ancestor who only appears on the path once. Display Displays additional relationship information via the extended 'Families' tab, and the extended 'Facts and Events' tab. Do not show any relationship Each SLCA (smallest lowest common ancestor) essentially represents a part of the tree which both individuals share (as part of their ancestors). More technically, the SLCA set of two individuals is a subset of the LCA set (excluding all LCAs that are themselves ancestors of other LCAs). Families Tab Settings Find a closest relationship via common ancestors Find a closest relationship via common ancestors, or fallback to the closest overall connection Find all overall connections Find all relationships via lowest common ancestors Find all smallest lowest common ancestors, show a closest connection for each Find other overall connections Find other/all overall connections Find the closest overall connections Find the closest overall connections (preferably via common ancestors) Finished - all link dates are up-to-date. For close relationships similar to the previous option, but faster. Internally just a combination of two other methods. For large trees, this process may initially take several minutes. You can always safely abort and continue later. How to determine relationships between parents How to determine relationships to associated persons How to determine relationships to spouses How to determine relationships to the default individual If this is unexpected, and there are recent changes, you may have to follow this link:  If this option is checked, relationships to the associated individuals are only shown for the following facts and events. If you do not want to change the functionality with respect to the original Facts and Events tab, select 'Do not show any relationship' in the first block. If you do not want to change the functionality with respect to the original Families tab, select 'Do not show any relationship' everywhere. If you do not want to use the chart functionality, hide this chart via Control Panel > Charts > %1$s Vesta Extended Relationships (note that the chart is listed under the module name). If you select this option everywhere, you should also disallow persistent toggle, as it has no visible effect. In particular if both lists are empty, relationships will never be shown for these facts and events. In that case, you should also disallow persistent toggle, as it has no visible effect. Individuals with Patriarchs Intended as a replacement for the original 'Relationships' module. It is more complicated to determine this exact CoR, and the differences are usually small anyway. Therefore, only the Uncorrected CoR is calculated. Legacy algorithm for Relationship path names Note that the facts and events to be displayed at all may be filtered via the preferences of the tab. Only show relationships for specific facts and events Options referring to overall connections established before %1$s. Options to show in the chart Patriarch Patriarchs are the male end-of-line ancestors ('Spitzenahnen'). Prefers partial paths via common ancestors, even if there is no direct common ancestor. Same option as in the original relationship chart, further configurable via recursion level: Same option as in the original relationship chart. Searching for regular overall connections would be pointless here because there is always a trivial HUSB - WIFE connection. Show common ancestors Show common ancestors on top of relationship paths Show legacy relationship path names Synchronization Synchronize trees to obtain dated relationship links The CoR (Coefficient of Relationship) is proportional to the number of genes that two individuals have in common as a result of their genetic relationship. Its calculation is based on Sewall Wright's method of path coefficients. Its value represents the proportion of genes held in common by two related individuals over and above those held in common by the whole population. More details here:  The following options refer to the same algorithms as used in the extended relationships chart: The following options use dated links, i.e. links that have been established before the date of the respective event. The process should be repeated (but will finish much faster) whenever a tree is edited, otherwise you may obtain inconsistent relationship data (displayed relationships are always valid, but they may not actually have been established at the given date, if changes in the tree are not synchronized here). The same information may be obtained via the branches list, where they show up as the heads of branches. Therefore, if one of the following options is selected, overall connections are determined via dated links, i.e. links that have been established before the date of the respective event. These relationships may only be calculated efficiently by preprocessing the tree data, via the synchronization link at the top of this page. This allows you to present meaningful connections, such as 'widowed husband marries the sister of his dead wife'. This list provides an overview by surname, and may be used to determine whether all individuals with a specific surname are actually descended from a common patriarch. This process calculates dates for all INDI - FAM links, so that relationships at a specific point in time can be calculated efficiently. This seems more useful in most cases (e.g. to determine the relationship to a godparent at the time of the baptism). Uncorrected CoR (Coefficient of Relationship) Uncorrected CoR (Coefficient of Relationship): %s Under normal circumstances the proportion of genes transmitted from ancestor to descendant &ndash; as estimated by Σ(0.5)<sup>n</sup> &ndash;  and the proportion of genes they hold in common (the true coefficient of relationship) are the same. If there has been any inbreeding, however, there may be a slight discrepancy, as explained here:  You can disable this via the module preferences, it's mainly intended for debugging. You may also adjust the access level of this part of the module. parents unlimited via legacy algorithm: %s Project-Id-Version: Norwegian Bokmål (Vesta Webtrees Custom Modules)
Report-Msgid-Bugs-To: 
PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE
Last-Translator: FULL NAME <EMAIL@ADDRESS>
Language-Team: Norwegian Bokmål <https://hosted.weblate.org/projects/vesta-webtrees-custom-modules/vesta-extended-relationships/nb_NO/>
Language: nb_NO
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=n != 1;
X-Generator: Weblate 4.15.1-dev
  (se nedenfor for mer informasjon). %1$s er %2$s til %3$s. (Antall relasjoner: %s) (totalt sett nesten like nært beslektet som %1$s) (totalt sett like nært beslektet som %1$s) (totalt sett nærmere beslektet enn %1$s) (totalt sett ikke vesentlig nærmere enn det nærmeste forholdet via felles aner) En modul for å bestemme relasjoner og slektskap. Inneholder et diagram som viser relasjoner mellom to personer, som kan erstatte "Slektskap"-modulen som følger med webtrees. En modul som tilbyr ulike algoritmer som brukes til å bestemme relasjoner. Inkluderer et utvidet «Slektskaps-diagram». Alle baner mellom de to personene som bidrar til CoR (Koeffisient av relasjon), som definert her: Tillat vedvarende valg (brukeren kan vise/skjule relasjoner) Og forhåpentlig viser det hvor mye bedre den nye algoritmen fungerer … Tilknyttede fakta og hendelser vises når det er merket av for respektive valgalternativer i fanen . I utgangspunktet, hver bane (via felles aner) mellom to individer bidrar til CoR, omvendt proporsjonal med lengden: Hver bane av lengde 2 (f.eks mellom fulle søsken) legger til %1$s, hver banelengde 4 (f.eks. mellom fettere) legger %2$s, generelt hver bane av lengde n legger til (0,5)<sup>n</sup>. Innstillinger for diagram Felles ane:  Felles aner:  Feilsøking Bestemmer den korteste veien mellom to personer via en LFS (laveste felles ane), det vil si at en felles ane bare vises på linjen en gang. Vis Viser ytterligere slektskap via den utvidede «Familier-fanen» og den utvidede «Fakta og hendelser»-fanen. Ikke vis noe slektskap Hver MLFA (minste laveste felles ane) representerer i hovedsak en del av treet som begge deler (som en del av sine aner). Mer teknisk sett er MLFA-settet med to personer et delsett av LFS-settet (unntatt alle LFSer som selv er aner til andre LFSer). Innstillinger for Familier-fanen Finn et nærmeste slektskap via felles aner Finn et nærmeste slektskap via felles aner, eller fall tilbake til nærmeste generelle forbindelse Finn alle generelle forbindelser Finn alle relasjoner via laveste felles aner Finn alle de nærmeste felles aner, vis en nærmeste forbindelse for hver Finn alle generelle forbindelser Finn alle generelle forbindelser Finn den nærmeste generelle forbindelsen Finn de nærmeste generelle forbindelsene (helst via felles aner) Ferdig - alle koblingsdatoer er oppdatert. For nære relasjoner som ligner på det forrige alternativet, men raskere. Internt bare en kombinasjon av to andre metoder. For store trær kan denne prosessen i utgangspunktet ta flere minutter. Du kan alltid trygt avbryte og fortsette senere. Hvordan bestemme forholdet mellom foreldre Hvordan fastslå relasjoner til tilknyttede personer Hvordan bestemme relasjoner til ektefeller Hvordan bestemme relasjonen til standardpersonen Hvis dette er uventet, og det er nylige endringer, må du kanskje følge denne koblingen:  Hvis det er merket av for dette alternativet, vises relasjoner til de tilknyttede personene bare for følgende fakta og hendelser. Hvis du ikke vil endre funksjonaliteten med hensyn til den opprinnelige kategorien Fakta og hendelser, velger du «Ikke vis noen relasjon» i den første blokken. Hvis du ikke vil endre funksjonaliteten med hensyn til den opprinnelige Familier-fanen, velger du «Ikke vis noen relasjon» overalt. Hvis du ikke vil bruke diagramfunksjonaliteten, skjuler du dette diagrammet via Kontrollpanel > Diagrammer > %1$s Vesta Extended Relationships (merk at diagrammet er oppført under modulnavnet). Hvis du velger dette alternativet overalt, bør du også forby vedvarende valg, da det ikke har noen synlig effekt. Hvis begge listene er tomme, vil relasjoner aldri bli vist for disse fakta og hendelser. I så fall bør du også velge bort vedvarende valg, da det ikke har noen synlig effekt. Personer med patriarker Ment som en erstatning for den opprinnelige "Slektskap"-modulen. Det er mer komplisert å bestemme denne eksakte CoR, og forskjellene er vanligvis små uansett. Derfor beregnes bare ukorrigert CoR. Tradisjonell algoritme for navn på slektskapsforbindelser Vær oppmerksom på at fakta og hendelser som skal vises i det hele tatt, kan filtreres via innstillingene i kategorien. Vis bare relasjoner for bestemte fakta og hendelser Alternativer som refererer til generelle tilkoblinger etablert før %1$s. Alternativer som skal vises i diagrammet Tidligste mannlige ane Patriarker er mannlige "ende-aner" (Stamfedre). Foretrekker delvise linjer via felles aner, selv om det ikke er noen direkte felles ane. Samme alternativ som i det opprinnelige relasjonsdiagrammet, ytterligere konfigurerbar via rekursjonsnivå: Samme alternativ som i det opprinnelige relasjonsdiagrammet. Å søke etter vanlige generelle forbindelser ville være meningsløst her fordi det alltid er en triviell HUSB - WIFE-forbindelse. Vis felles aner Vis felles aner på toppen av relasjonslinjer Vis tradisjonelle navn på slektskapsforbindelser Synkronisering Synkroniser trær for å få daterte relasjonskoblinger CoR (Koeffisient av forholdet) er proporsjonal med antall gener som to personer har til felles som følge av deres genetiske forhold. Beregningen er basert på Sewall Wrights metode for banekoeffisienter. Dens verdi representerer andelen gener som bæres felles av to beslektede individer utover de som bæres til felles av hele befolkningen. Flere detaljer her:  Følgende alternativer refererer til de samme algoritmene som brukes i diagrammet med utvidede forhold: Følgende alternativer bruker daterte koblinger, det vil si koblinger som er opprettet før datoen for den respektive hendelsen. Prosessen bør gjentas (men vil fullføre mye raskere) når et tre redigeres, ellers kan du få inkonsistente relasjonsdata (viste relasjoner er alltid gyldige, men de kan faktisk ikke ha blitt etablert på den angitte datoen, hvis endringer i treet ikke synkroniseres her). Den samme informasjonen kan bli funnet i listen over grener, hvor de vises som greners overhoder. Hvis ett av følgende alternativer er valgt, bestemmes derfor generelle tilkoblinger via daterte koblinger, det vil si koblinger som er opprettet før datoen for den respektive hendelsen. Disse relasjonene kan bare beregnes effektivt ved å forhåndsbehandle slektsdataene via synkroniseringskoblingen øverst på denne siden. Dette gjør at du kan presentere meningsfulle forbindelser, for eksempel "enkemann gifter seg med søsteren til sin døde kone". Denne listen tilbyr en oversikt basert på etternavn, og kan benyttes til å avgjøre om alle personer med et gitt etternavn virkelig nedstammer fra en felles patriark. Denne prosessen beregner datoer for alle INDI - FAM-koblinger, slik at relasjoner på et bestemt tidspunkt kan beregnes effektivt. Dette virker mer nyttig i de fleste tilfeller (f.eks. å bestemme forholdet til en fadder på tidspunktet for dåp). Ukorrigert Cor (koeffisient av slektskap) Ukorrigert Cor (Koeffisient av slektskap): %s Under normale omstendigheter er andelen gener som overføres fra aner til etterkommer &ndash; som estimert av Σ(0.5)<sup>n</sup> &ndash; og andelen gener de har til felles (den sanne koeffisienten i forholdet) er den samme. Hvis det har vært innavl, kan det imidlertid være en liten uoverensstemmelse, som forklart her:  Dette kan deaktiveres i modulinnstillingene; det brukes hovedsakelig for feilsøking. Du kan også justere tilgangsnivået for denne delen av modulen. foreldre ubegrenset ved tradisjonell algoritme: %s 