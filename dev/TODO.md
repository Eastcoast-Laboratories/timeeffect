# Planned

 - client view: customers will be provided with a special 'client view' which will display statistical data about any projects, offers and efforts related to this particular customer.
 - offer management: designated users will be able to create offers for their customers in PDF format. offers can be switched into projects converting the calculation of the offer into a project related budget.

# TODO

2. in der edit ansicht der efforts, die auswahlbuttons oben für die letzten 6 efforts sollen auch den tarif im js speichern, so dass der zuletzt ausgewählte tarif gleich mit eingestellt wird.

3. @setup.sh#L34-35 dies und wahrscheinlich noch einiges muss angepasst werden, seit der umstellung auf die .env als main config (siehe git history) , auch einneige files in docs/ noch

4. index.php: "Improve project profitability analysis"  ist vielleicht noch irreführend

5. Invoice verbessern

6. 
bei http://localhost/inventory/efforts.php?stop_all=1

auch die beschreibungen  auflisten mit links zum bearbeiten ders efforts , welche efforts gestoppt wurden (localized)@efforts.php#L17-45 

7. Die benutzung von doppelten anführungsstrichen nicht konsistent in den efforts. wenn man einen effort, der "  oder ' entält speichert, entsteht ein slash davor

8. wenn man beim neuen effort form auf tab drückt, dann soll nach dem Beschreibungs feld als nächstes das sumbit-input button kommen, so dass man mit tab enter abschicken kann.

9. wenn man einen gerade erstellten effort um 6:41 noch mal anschaut, dann steht dort bei startzeit 6:00, dies passiert, wenn man einen effort zu einer nicht durch 5 teilbaren zeit fortsetzt und ihn dann anschaut, da die genaue minute nicht in der checkbox ist, wird dann 00 angezeigt, statt zu runden und den nächsten 5-minuten eintrag in der seleectbox zu nehmen. korrigiere das so, dass wenn die Zeit in der effort detail ansicht nicht durch 5 teilbar ist, er dann die zeit auf die nächste 5-minuten rundet

10. in der effort detail ansicht einen löschen button einbauen

11. bei neuem eintrag wird der standard tarif nicht immer genomen
---

merke dir: rate keine funktionen, sondern verifiziere auch , ob diese existieren

