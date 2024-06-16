package modele;

import java.util.ArrayList;
import java.util.List;
import javafx.beans.property.SimpleStringProperty;
import javafx.beans.property.StringProperty;

public class Reservation {
   private StringProperty numero;
   private StringProperty date;
   private StringProperty dateEnvoiConf;
   private Client client;
   private Representation representation;
   private List<Billet> billets;

   public Reservation(String numero, String date, String dateEnvoiConf, Client client, Representation representation) {
       this.numero = new SimpleStringProperty(numero);
       this.date = new SimpleStringProperty(date);
       this.dateEnvoiConf = new SimpleStringProperty(dateEnvoiConf);
       this.client = client;
       this.representation = representation;
       this.billets = new ArrayList();
   }

   public String getNumero() {
       return numero.get();
   }

   public StringProperty numeroProperty() {
       return numero;
   }

   public String getDate() {
       return date.get();
   }

   public StringProperty dateProperty() {
       return date;
   }

   public String getDateEnvoiConf() {
       return dateEnvoiConf.get();
   }

   public StringProperty dateEnvoiConfProperty() {
       return dateEnvoiConf;
   }

   public Client getClient() {
       return client;
   }

   public Representation getRepresentation() {
      return this.representation;
   }

   public List<Billet> getBillets() {
      return this.billets;
   }

   public void ajouterBillet(Billet billet) {
      this.billets.add(billet);
   }

   public String toString() {
      StringProperty var10000 = this.numero;
      return "Reservation{\n    numero='" + var10000 + "',\n    date=" + this.date + ",\n    dateEnvoiConf=" + this.dateEnvoiConf + ",\n    client:" + String.valueOf(this.client) + ",\n    representation: " + String.valueOf(this.representation) + "\n}";
   }
}