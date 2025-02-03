<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203160809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exchange (id SERIAL NOT NULL, sender_id INT NOT NULL, receiver_id INT DEFAULT NULL, executed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D33BB079F624B39D ON exchange (sender_id)');
        $this->addSql('CREATE INDEX IDX_D33BB079CD53EDB6 ON exchange (receiver_id)');
        $this->addSql('CREATE TABLE gain_cards (exchange_id INT NOT NULL, pokemon_card_id INT NOT NULL, PRIMARY KEY(exchange_id, pokemon_card_id))');
        $this->addSql('CREATE INDEX IDX_B0BC7B7268AFD1A0 ON gain_cards (exchange_id)');
        $this->addSql('CREATE INDEX IDX_B0BC7B7226A6E6B1 ON gain_cards (pokemon_card_id)');
        $this->addSql('CREATE TABLE given_cards (exchange_id INT NOT NULL, pokemon_card_id INT NOT NULL, PRIMARY KEY(exchange_id, pokemon_card_id))');
        $this->addSql('CREATE INDEX IDX_C0992B1768AFD1A0 ON given_cards (exchange_id)');
        $this->addSql('CREATE INDEX IDX_C0992B1726A6E6B1 ON given_cards (pokemon_card_id)');
        $this->addSql('CREATE TABLE pokemon_card (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, img_url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_vip BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_pokemon_card (user_id INT NOT NULL, pokemon_card_id INT NOT NULL, PRIMARY KEY(user_id, pokemon_card_id))');
        $this->addSql('CREATE INDEX IDX_AA719A92A76ED395 ON user_pokemon_card (user_id)');
        $this->addSql('CREATE INDEX IDX_AA719A9226A6E6B1 ON user_pokemon_card (pokemon_card_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE exchange ADD CONSTRAINT FK_D33BB079F624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE exchange ADD CONSTRAINT FK_D33BB079CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gain_cards ADD CONSTRAINT FK_B0BC7B7268AFD1A0 FOREIGN KEY (exchange_id) REFERENCES exchange (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gain_cards ADD CONSTRAINT FK_B0BC7B7226A6E6B1 FOREIGN KEY (pokemon_card_id) REFERENCES pokemon_card (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE given_cards ADD CONSTRAINT FK_C0992B1768AFD1A0 FOREIGN KEY (exchange_id) REFERENCES exchange (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE given_cards ADD CONSTRAINT FK_C0992B1726A6E6B1 FOREIGN KEY (pokemon_card_id) REFERENCES pokemon_card (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_pokemon_card ADD CONSTRAINT FK_AA719A92A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_pokemon_card ADD CONSTRAINT FK_AA719A9226A6E6B1 FOREIGN KEY (pokemon_card_id) REFERENCES pokemon_card (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE exchange DROP CONSTRAINT FK_D33BB079F624B39D');
        $this->addSql('ALTER TABLE exchange DROP CONSTRAINT FK_D33BB079CD53EDB6');
        $this->addSql('ALTER TABLE gain_cards DROP CONSTRAINT FK_B0BC7B7268AFD1A0');
        $this->addSql('ALTER TABLE gain_cards DROP CONSTRAINT FK_B0BC7B7226A6E6B1');
        $this->addSql('ALTER TABLE given_cards DROP CONSTRAINT FK_C0992B1768AFD1A0');
        $this->addSql('ALTER TABLE given_cards DROP CONSTRAINT FK_C0992B1726A6E6B1');
        $this->addSql('ALTER TABLE user_pokemon_card DROP CONSTRAINT FK_AA719A92A76ED395');
        $this->addSql('ALTER TABLE user_pokemon_card DROP CONSTRAINT FK_AA719A9226A6E6B1');
        $this->addSql('DROP TABLE exchange');
        $this->addSql('DROP TABLE gain_cards');
        $this->addSql('DROP TABLE given_cards');
        $this->addSql('DROP TABLE pokemon_card');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_pokemon_card');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
