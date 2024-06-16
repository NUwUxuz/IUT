package modele;

import javafx.beans.property.SimpleStringProperty;
import javafx.beans.property.StringProperty;


public class Client {
   private StringProperty nom;
   private StringProperty prenom;
   private StringProperty ville;
   private StringProperty adresse;
   private StringProperty tel;
   private StringProperty mail;
   private StringProperty numero;

   public Client(String nom, String prenom, String ville, String adresse, String tel, String mail, String numero) {
       this.nom = new SimpleStringProperty(nom);
       this.prenom = new SimpleStringProperty(prenom);
       this.ville = new SimpleStringProperty(ville);
       this.adresse = new SimpleStringProperty(adresse);
       this.tel = new SimpleStringProperty(tel);
       this.mail = new SimpleStringProperty(mail);
       this.numero = new SimpleStringProperty(numero);
   }

   public String getNom() {
       return nom.get();
   }

   public StringProperty nomProperty() {
       return nom;
   }

   public String getPrenom() {
       return prenom.get();
   }

   public StringProperty prenomProperty() {
       return prenom;
   }

   public String getVille() {
       return ville.get();
   }

   public StringProperty villeProperty() {
       return ville;
   }

   public String getAdresse() {
       return adresse.get();
   }

   public StringProperty adresseProperty() {
       return adresse;
   }

   public String getTel() {
       return tel.get();
   }

   public StringProperty telProperty() {
       return tel;
   }

   public String getMail() {
       return mail.get();
   }

   public StringProperty mailProperty() {
       return mail;
   }

   public String getNumero() {
       return numero.get();
   }

   public StringProperty numeroProperty() {
       return numero;
   }

   @Override
   public String toString() {
       return " nom='" + nom.get() + "', prenom='" + prenom.get() + "', ville='" + ville.get() + "', adresse='" + adresse.get() + "', Tel='" + tel.get() + "', mail='" + mail.get() + "', numero='" + numero.get() + "'";
   }
}