<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ergonode\Migration;

use Doctrine\DBAL\Schema\Schema;

/**
* Auto-generated Ergonode Migration Class:
*/
final class Version20210505070030 extends AbstractErgonodeMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE exporter.shopware6_product_relation_attribute(
                    channel_id uuid NOT NULL,
                    product_id uuid NOT NULL,
                    attribute_id uuid NOT NULL,
                    shopware6_id varchar(36) NOT NULL,
                    updated_at timestamp with time zone NOT NULL,
                    PRIMARY KEY (channel_id, product_id, attribute_id)
                )'
        );

        $this->addSql(
            'ALTER TABLE exporter.shopware6_product_relation_attribute 
                    ADD CONSTRAINT shopware6_product_relation_attribute_fk FOREIGN KEY (channel_id) 
                    REFERENCES exporter.channel(id) ON DELETE CASCADE'
        );

        $this->addSql(
            'UPDATE exporter.channel
                    SET "configuration" = jsonb_set("configuration",\'{product_relation_attributes}\',\'[]\',true)
                    WHERE type = \'shopware-6-api\''
        );
    }
}
