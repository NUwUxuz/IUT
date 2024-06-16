package modele;

import javafx.beans.property.SimpleStringProperty;
import javafx.beans.property.StringProperty;

public class Representation {
    private StringProperty jour;
    private StringProperty heure;
    private boolean annulee;

    public Representation(String jour, String heure, boolean annulee) {
        this.jour = new SimpleStringProperty (jour);
        this.heure = new SimpleStringProperty (heure);
        this.annulee = annulee;
    }

    public String getJour() {
        return jour.get();
    }
    
    public StringProperty jourProperty() {
        return jour;
    }

    public String getHeure() {
        return heure.get();
    }
    
    public StringProperty heureProperty() {
        return heure;
    }

    public boolean isAnnulee() {
        return annulee;
    }

    @Override
    public String toString() {
        return "jour='" + jour + '\'' +
                ", heure='" + heure + '\'' +
                ", annulee=" + annulee;
    }
    
}