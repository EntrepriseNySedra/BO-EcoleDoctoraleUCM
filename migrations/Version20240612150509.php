<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612150509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publication_realisation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, user VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE success (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, user VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE temoignage (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, user VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
       /* $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFFDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFF4272FC9F FOREIGN KEY (domaine_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFF7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFFB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFF6E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFF62E883B1 FOREIGN KEY (ue_id) REFERENCES unite_enseignements (id)');
        $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFF544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE absences ADD CONSTRAINT FK_F9C0EFFFC13CD51E FOREIGN KEY (emploi_du_temps_id) REFERENCES emploi_du_temps (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C64CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C647A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C64B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C646E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C6441D534A9 FOREIGN KEY (unite_enseignements_id) REFERENCES unite_enseignements (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C64F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C645577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C64544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C64AA23F281 FOREIGN KEY (surveillant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE calendrier_examen ADD CONSTRAINT FK_E8634C6460BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE calendrier_examen_historique ADD CONSTRAINT FK_DFA4A4B5A1E896CF FOREIGN KEY (calendrier_examen_id) REFERENCES calendrier_examen (id)');
        $this->addSql('ALTER TABLE calendrier_examen_historique ADD CONSTRAINT FK_DFA4A4B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE candidature_historique ADD CONSTRAINT FK_7EA1F295B6121583 FOREIGN KEY (candidature_id) REFERENCES concours_candidature (id)');
        $this->addSql('ALTER TABLE candidature_historique ADD CONSTRAINT FK_7EA1F295A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE concours_matiere ADD CONSTRAINT FK_71A45644D11E3C7 FOREIGN KEY (concours_id) REFERENCES concours (id)');
        $this->addSql('ALTER TABLE concours_matiere ADD CONSTRAINT FK_71A456447A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE concours_matiere ADD CONSTRAINT FK_71A456446E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CF46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE cours_media ADD CONSTRAINT FK_5A4A28DA7ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE cours_section ADD CONSTRAINT FK_6E7513947ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('DROP INDEX IDX_F86B32C1E455FCC0 ON emploi_du_temps');
        $this->addSql('ALTER TABLE emploi_du_temps DROP enseignant_id');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C1F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C1B11E4946 FOREIGN KEY (salles_id) REFERENCES salles (id)');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C1B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C17A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C16E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C162E883B1 FOREIGN KEY (ue_id) REFERENCES unite_enseignements (id)');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C15577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
        $this->addSql('ALTER TABLE emploi_du_temps ADD CONSTRAINT FK_F86B32C1544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE employe ADD CONSTRAINT FK_F804D3B9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE enseignant_mention ADD CONSTRAINT FK_41C7AEA37A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE enseignant_mention ADD CONSTRAINT FK_41C7AEA3B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE enseignant_mention ADD CONSTRAINT FK_41C7AEA3E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignant (id)');
        $this->addSql('ALTER TABLE enseignant_mention ADD CONSTRAINT FK_41C7AEA36E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE etudiant RENAME INDEX uniq_717e22e39ea77730 TO UNIQ_717E22E390BC5EA3');
        $this->addSql('ALTER TABLE frais_scolarite ADD CONSTRAINT FK_343C1D6EDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE frais_scolarite ADD CONSTRAINT FK_343C1D6E7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE frais_scolarite ADD CONSTRAINT FK_343C1D6EB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE frais_scolarite ADD CONSTRAINT FK_343C1D6E6E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE frais_scolarite ADD CONSTRAINT FK_343C1D6E5577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
        $this->addSql('ALTER TABLE frais_scolarite ADD CONSTRAINT FK_343C1D6E544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE frais_scolarite ADD CONSTRAINT FK_343C1D6EF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D67A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D66E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6F40B33B5 FOREIGN KEY (frais_scolarite_id) REFERENCES frais_scolarite (id)');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574A41D534A9 FOREIGN KEY (unite_enseignements_id) REFERENCES unite_enseignements (id)');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574AE455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignant (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FADEEA87261 FOREIGN KEY (type_prestation_id) REFERENCES type_prestation (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL'); */
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE publication_realisation');
        $this->addSql('DROP TABLE success');
        $this->addSql('DROP TABLE temoignage');
       /* $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFFDDEAB1A3');
        $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFF4272FC9F');
        $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFF7A4147F0');
        $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFFB3E9C81');
        $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFF6E38C0DB');
        $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFF62E883B1');
        $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFF544BFD58');
        $this->addSql('ALTER TABLE absences DROP FOREIGN KEY FK_F9C0EFFFC13CD51E');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C64CCF9E01E');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C647A4147F0');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C64B3E9C81');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C646E38C0DB');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C6441D534A9');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C64F46CD258');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C645577AFDB');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C64544BFD58');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C64AA23F281');
        $this->addSql('ALTER TABLE calendrier_examen DROP FOREIGN KEY FK_E8634C6460BB6FE6');
        $this->addSql('ALTER TABLE calendrier_examen_historique DROP FOREIGN KEY FK_DFA4A4B5A1E896CF');
        $this->addSql('ALTER TABLE calendrier_examen_historique DROP FOREIGN KEY FK_DFA4A4B5A76ED395');
        $this->addSql('ALTER TABLE candidature_historique DROP FOREIGN KEY FK_7EA1F295B6121583');
        $this->addSql('ALTER TABLE candidature_historique DROP FOREIGN KEY FK_7EA1F295A76ED395');
        $this->addSql('ALTER TABLE concours_matiere DROP FOREIGN KEY FK_71A45644D11E3C7');
        $this->addSql('ALTER TABLE concours_matiere DROP FOREIGN KEY FK_71A456447A4147F0');
        $this->addSql('ALTER TABLE concours_matiere DROP FOREIGN KEY FK_71A456446E38C0DB');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CF46CD258');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C544BFD58');
        $this->addSql('ALTER TABLE cours_media DROP FOREIGN KEY FK_5A4A28DA7ECF78B0');
        $this->addSql('ALTER TABLE cours_section DROP FOREIGN KEY FK_6E7513947ECF78B0');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C1F46CD258');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C1B11E4946');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C1B3E9C81');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C17A4147F0');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C16E38C0DB');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C162E883B1');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C15577AFDB');
        $this->addSql('ALTER TABLE emploi_du_temps DROP FOREIGN KEY FK_F86B32C1544BFD58');
        $this->addSql('ALTER TABLE emploi_du_temps ADD enseignant_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_F86B32C1E455FCC0 ON emploi_du_temps (enseignant_id)');
        $this->addSql('ALTER TABLE employe DROP FOREIGN KEY FK_F804D3B9A76ED395');
        $this->addSql('ALTER TABLE enseignant_mention DROP FOREIGN KEY FK_41C7AEA37A4147F0');
        $this->addSql('ALTER TABLE enseignant_mention DROP FOREIGN KEY FK_41C7AEA3B3E9C81');
        $this->addSql('ALTER TABLE enseignant_mention DROP FOREIGN KEY FK_41C7AEA3E455FCC0');
        $this->addSql('ALTER TABLE enseignant_mention DROP FOREIGN KEY FK_41C7AEA36E38C0DB');
        $this->addSql('ALTER TABLE etudiant RENAME INDEX uniq_717e22e390bc5ea3 TO UNIQ_717E22E39EA77730');
        $this->addSql('ALTER TABLE frais_scolarite DROP FOREIGN KEY FK_343C1D6EDDEAB1A3');
        $this->addSql('ALTER TABLE frais_scolarite DROP FOREIGN KEY FK_343C1D6E7A4147F0');
        $this->addSql('ALTER TABLE frais_scolarite DROP FOREIGN KEY FK_343C1D6EB3E9C81');
        $this->addSql('ALTER TABLE frais_scolarite DROP FOREIGN KEY FK_343C1D6E6E38C0DB');
        $this->addSql('ALTER TABLE frais_scolarite DROP FOREIGN KEY FK_343C1D6E5577AFDB');
        $this->addSql('ALTER TABLE frais_scolarite DROP FOREIGN KEY FK_343C1D6E544BFD58');
        $this->addSql('ALTER TABLE frais_scolarite DROP FOREIGN KEY FK_343C1D6EF675F31B');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6DDEAB1A3');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6544BFD58');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D67A4147F0');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6B3E9C81');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D66E38C0DB');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6F40B33B5');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574A41D534A9');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574AE455FCC0');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FADEEA87261');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD544BFD58');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD60BB6FE6');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FADA76ED395');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD7A4147F0');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');   */
    }
}
