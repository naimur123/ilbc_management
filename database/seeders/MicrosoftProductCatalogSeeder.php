<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSku;
use Illuminate\Database\Seeder;

/**
 * Seeds the Microsoft business product catalog described in the design
 * addendum: Microsoft 365, Azure (by category), Dynamics 365, Power
 * Platform, Windows Server / SQL Server, Enterprise Mobility + Security,
 * and a few cross-cutting items (Windows 365, Copilot).
 *
 * This is business-catalog depth — the products actually quoted and
 * resold to customers — not the exhaustive, constantly-changing Azure
 * meter/price list. See the design document's note on
 * AzureRetailPriceSyncJob for how to layer exact Azure meter pricing on
 * top of this catalog later without changing this seeder.
 *
 * Idempotent: safe to re-run, matches on `code` / `sku_code`.
 */
class MicrosoftProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalog() as $categoryDef) {
            $category = ProductCategory::updateOrCreate(
                ['code' => $categoryDef['code']],
                [
                    'name' => $categoryDef['name'],
                    'publisher' => 'Microsoft',
                    'sort_order' => $categoryDef['sort_order'] ?? 0,
                    'is_active' => true,
                ]
            );

            foreach ($categoryDef['products'] as $productDef) {
                $product = Product::updateOrCreate(
                    [
                        'product_category_id' => $category->id,
                        'name' => $productDef['name'],
                    ],
                    [
                        'product_line' => $categoryDef['product_line'],
                        'segment' => $productDef['segment'] ?? 'ALL',
                        'licensing_program' => $productDef['licensing_program'] ?? 'CSP_NCE',
                        'description' => $productDef['description'] ?? null,
                        'is_active' => true,
                    ]
                );

                foreach ($productDef['skus'] as $skuDef) {
                    ProductSku::updateOrCreate(
                        ['sku_code' => $skuDef['code']],
                        [
                            'product_id' => $product->id,
                            'billing_model' => $skuDef['billing_model'] ?? 'SEAT_BASED',
                            'consumption_unit' => $skuDef['consumption_unit'] ?? null,
                            'term' => $skuDef['term'] ?? 'MONTHLY',
                            'min_seats' => $skuDef['min_seats'] ?? null,
                            'billing_cycle_options' => $skuDef['billing_cycle_options'] ?? ['monthly', 'annual'],
                            'supports_trial' => $skuDef['supports_trial'] ?? false,
                            'is_renewable' => $skuDef['is_renewable'] ?? true,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @return array<int, array{
     *   code: string, name: string, product_line: string, sort_order?: int,
     *   products: array<int, array{
     *     name: string, segment?: string, licensing_program?: string, description?: string,
     *     skus: array<int, array{code: string, billing_model?: string, consumption_unit?: string, term?: string}>
     *   }>
     * }>
     */
    private function catalog(): array
    {
        return [

            // ---------------------------------------------------------------
            // Microsoft 365
            // ---------------------------------------------------------------
            [
                'code' => 'M365',
                'name' => 'Microsoft 365',
                'product_line' => 'M365',
                'sort_order' => 10,
                'products' => [
                    ['name' => 'Microsoft 365 Business Basic', 'segment' => 'SMB', 'skus' => [
                        ['code' => 'M365-BB'],
                    ]],
                    ['name' => 'Microsoft 365 Business Standard', 'segment' => 'SMB', 'skus' => [
                        ['code' => 'M365-BS'],
                    ]],
                    ['name' => 'Microsoft 365 Business Premium', 'segment' => 'SMB', 'skus' => [
                        ['code' => 'M365-BP'],
                    ]],
                    ['name' => 'Microsoft 365 Apps for Business', 'segment' => 'SMB', 'skus' => [
                        ['code' => 'M365-APPS-BIZ'],
                    ]],
                    ['name' => 'Microsoft 365 Apps for Enterprise', 'segment' => 'ENTERPRISE', 'skus' => [
                        ['code' => 'M365-APPS-ENT'],
                    ]],
                    ['name' => 'Microsoft 365 E3', 'segment' => 'ENTERPRISE', 'skus' => [
                        ['code' => 'M365-E3'],
                    ]],
                    ['name' => 'Microsoft 365 E5', 'segment' => 'ENTERPRISE', 'skus' => [
                        ['code' => 'M365-E5'],
                    ]],
                    ['name' => 'Microsoft 365 F3', 'segment' => 'FRONTLINE', 'skus' => [
                        ['code' => 'M365-F3'],
                    ]],
                    ['name' => 'Exchange Online', 'segment' => 'ALL', 'skus' => [
                        ['code' => 'EXO-P1'], ['code' => 'EXO-P2'],
                    ]],
                    ['name' => 'SharePoint Online', 'segment' => 'ALL', 'skus' => [
                        ['code' => 'SPO-P1'], ['code' => 'SPO-P2'],
                    ]],
                    ['name' => 'OneDrive for Business', 'segment' => 'ALL', 'skus' => [
                        ['code' => 'ODB-P1'], ['code' => 'ODB-P2'],
                    ]],
                    ['name' => 'Microsoft Teams', 'segment' => 'ALL', 'skus' => [
                        ['code' => 'TEAMS-ESS'],
                        ['code' => 'TEAMS-PREM'],
                        ['code' => 'TEAMS-PHONE'],
                        ['code' => 'TEAMS-AUDIOCONF'],
                    ]],
                    ['name' => 'Visio', 'segment' => 'ALL', 'skus' => [
                        ['code' => 'VISIO-P1'], ['code' => 'VISIO-P2'],
                    ]],
                    ['name' => 'Project', 'segment' => 'ALL', 'skus' => [
                        ['code' => 'PROJ-P1'], ['code' => 'PROJ-P3'], ['code' => 'PROJ-P5'],
                    ]],
                    ['name' => 'Microsoft 365 Copilot', 'segment' => 'ALL', 'description' => 'Add-on; requires a qualifying base license.', 'skus' => [
                        ['code' => 'M365-COPILOT'],
                    ]],
                ],
            ],

            // ---------------------------------------------------------------
            // Azure — grouped by service category
            // ---------------------------------------------------------------
            [
                'code' => 'AZURE',
                'name' => 'Azure',
                'product_line' => 'AZURE',
                'sort_order' => 20,
                'products' => [
                    ['name' => 'Azure Virtual Machines', 'skus' => $this->consumptionSkus(['AZ-VM-B2S', 'AZ-VM-D2SV5', 'AZ-VM-D4SV5', 'AZ-VM-E2SV5'], 'vCPU-hour')],
                    ['name' => 'Azure App Service', 'skus' => $this->consumptionSkus(['AZ-APPSVC-B1', 'AZ-APPSVC-S1', 'AZ-APPSVC-P1V3'], 'hour')],
                    ['name' => 'Azure Kubernetes Service (AKS)', 'skus' => $this->consumptionSkus(['AZ-AKS-STD'], 'vCPU-hour')],
                    ['name' => 'Azure Functions', 'skus' => $this->consumptionSkus(['AZ-FUNC-CONSUMPTION'], '1M executions')],
                    ['name' => 'Azure Virtual Machine Scale Sets', 'skus' => $this->consumptionSkus(['AZ-VMSS-STD'], 'vCPU-hour')],

                    ['name' => 'Azure Blob Storage', 'skus' => $this->consumptionSkus(['AZ-BLOB-HOT', 'AZ-BLOB-COOL', 'AZ-BLOB-ARCHIVE'], 'GB-month')],
                    ['name' => 'Azure Files', 'skus' => $this->consumptionSkus(['AZ-FILES-STD'], 'GB-month')],
                    ['name' => 'Azure Managed Disks', 'skus' => $this->consumptionSkus(['AZ-DISK-SSD', 'AZ-DISK-HDD'], 'GB-month')],

                    ['name' => 'Azure Virtual Network', 'skus' => $this->consumptionSkus(['AZ-VNET-PEERING'], 'GB')],
                    ['name' => 'Azure VPN Gateway', 'skus' => $this->consumptionSkus(['AZ-VPNGW-STD'], 'hour')],
                    ['name' => 'Azure ExpressRoute', 'skus' => $this->consumptionSkus(['AZ-EXPRESSROUTE-STD'], 'hour')],
                    ['name' => 'Azure Front Door / CDN', 'skus' => $this->consumptionSkus(['AZ-FRONTDOOR-STD'], 'GB')],
                    ['name' => 'Azure Load Balancer', 'skus' => $this->consumptionSkus(['AZ-LB-STD'], 'hour')],
                    ['name' => 'Azure Firewall', 'skus' => $this->consumptionSkus(['AZ-FIREWALL-STD'], 'hour')],

                    ['name' => 'Azure SQL Database', 'skus' => $this->consumptionSkus(['AZ-SQLDB-STD', 'AZ-SQLDB-PREMIUM'], 'vCore-hour')],
                    ['name' => 'Azure SQL Managed Instance', 'skus' => $this->consumptionSkus(['AZ-SQLMI-GP'], 'vCore-hour')],
                    ['name' => 'Azure Cosmos DB', 'skus' => $this->consumptionSkus(['AZ-COSMOSDB-RU'], '100 RU/s-hour')],
                    ['name' => 'Azure Database for MySQL', 'skus' => $this->consumptionSkus(['AZ-MYSQL-FLEX'], 'vCore-hour')],
                    ['name' => 'Azure Database for PostgreSQL', 'skus' => $this->consumptionSkus(['AZ-POSTGRES-FLEX'], 'vCore-hour')],

                    ['name' => 'Azure OpenAI Service', 'skus' => $this->consumptionSkus(['AZ-OPENAI-GPT'], '1K tokens')],
                    ['name' => 'Azure AI (Cognitive) Services', 'skus' => $this->consumptionSkus(['AZ-COGSVC-VISION', 'AZ-COGSVC-SPEECH'], 'transaction')],
                    ['name' => 'Azure Machine Learning', 'skus' => $this->consumptionSkus(['AZ-AML-COMPUTE'], 'vCPU-hour')],

                    ['name' => 'Azure Synapse Analytics', 'skus' => $this->consumptionSkus(['AZ-SYNAPSE-DWU'], 'DWU-hour')],
                    ['name' => 'Azure Data Factory', 'skus' => $this->consumptionSkus(['AZ-ADF-PIPELINE'], 'activity run')],
                    ['name' => 'Azure Databricks', 'skus' => $this->consumptionSkus(['AZ-DATABRICKS-DBU'], 'DBU-hour')],
                    ['name' => 'Azure Data Lake Storage', 'skus' => $this->consumptionSkus(['AZ-DATALAKE-GEN2'], 'GB-month')],

                    ['name' => 'Microsoft Sentinel', 'skus' => $this->consumptionSkus(['AZ-SENTINEL-GB'], 'GB ingested')],
                    ['name' => 'Microsoft Defender for Cloud', 'skus' => $this->consumptionSkus(['AZ-DEFENDER-CLOUD'], 'resource-month')],
                    ['name' => 'Azure Key Vault', 'skus' => $this->consumptionSkus(['AZ-KEYVAULT-OPS'], '10K operations')],

                    ['name' => 'Microsoft Entra ID (Azure AD Premium)', 'skus' => [
                        ['code' => 'AZ-ENTRA-P1'], ['code' => 'AZ-ENTRA-P2'],
                    ]],

                    ['name' => 'Azure Backup', 'skus' => $this->consumptionSkus(['AZ-BACKUP-INSTANCE'], 'instance-month')],
                    ['name' => 'Azure Site Recovery', 'skus' => $this->consumptionSkus(['AZ-ASR-INSTANCE'], 'instance-month')],

                    ['name' => 'Azure Logic Apps', 'skus' => $this->consumptionSkus(['AZ-LOGICAPPS-CONSUMPTION'], 'action execution')],
                    ['name' => 'Azure API Management', 'skus' => $this->consumptionSkus(['AZ-APIM-STD'], 'unit-hour')],
                    ['name' => 'Azure Service Bus', 'skus' => $this->consumptionSkus(['AZ-SVCBUS-STD'], 'million operations')],
                    ['name' => 'Azure Event Grid', 'skus' => $this->consumptionSkus(['AZ-EVENTGRID-OPS'], 'million operations')],

                    ['name' => 'Azure DevOps', 'skus' => [
                        ['code' => 'AZ-DEVOPS-BASIC'], ['code' => 'AZ-DEVOPS-BASIC-TESTPLANS'],
                    ]],

                    ['name' => 'Azure IoT Hub', 'skus' => $this->consumptionSkus(['AZ-IOTHUB-S1'], 'unit-month')],
                    ['name' => 'Azure IoT Central', 'skus' => $this->consumptionSkus(['AZ-IOTCENTRAL-STD'], 'device-month')],

                    ['name' => 'Azure Monitor / Log Analytics / Application Insights', 'skus' => $this->consumptionSkus(['AZ-MONITOR-GB', 'AZ-APPINSIGHTS-GB'], 'GB ingested')],

                    ['name' => 'Windows 365 Cloud PC', 'segment' => 'ALL', 'skus' => [
                        ['code' => 'W365-2VCPU-8GB', 'billing_model' => 'SEAT_BASED'],
                        ['code' => 'W365-4VCPU-16GB', 'billing_model' => 'SEAT_BASED'],
                        ['code' => 'W365-8VCPU-32GB', 'billing_model' => 'SEAT_BASED'],
                    ]],
                ],
            ],

            // ---------------------------------------------------------------
            // Dynamics 365
            // ---------------------------------------------------------------
            [
                'code' => 'D365',
                'name' => 'Dynamics 365',
                'product_line' => 'DYNAMICS365',
                'sort_order' => 30,
                'products' => [
                    ['name' => 'Dynamics 365 Sales', 'skus' => [['code' => 'D365-SALES-ENT'], ['code' => 'D365-SALES-PRO']]],
                    ['name' => 'Dynamics 365 Customer Service', 'skus' => [['code' => 'D365-CS-ENT'], ['code' => 'D365-CS-PRO']]],
                    ['name' => 'Dynamics 365 Field Service', 'skus' => [['code' => 'D365-FIELDSVC']]],
                    ['name' => 'Dynamics 365 Finance', 'skus' => [['code' => 'D365-FINANCE']]],
                    ['name' => 'Dynamics 365 Supply Chain Management', 'skus' => [['code' => 'D365-SCM']]],
                    ['name' => 'Dynamics 365 Business Central', 'skus' => [['code' => 'D365-BC-ESSENTIALS'], ['code' => 'D365-BC-PREMIUM']]],
                    ['name' => 'Dynamics 365 Marketing', 'skus' => [['code' => 'D365-MARKETING']]],
                    ['name' => 'Dynamics 365 Customer Insights', 'skus' => [['code' => 'D365-CUSTOMER-INSIGHTS']]],
                ],
            ],

            // ---------------------------------------------------------------
            // Power Platform
            // ---------------------------------------------------------------
            [
                'code' => 'POWERPLATFORM',
                'name' => 'Power Platform',
                'product_line' => 'POWER_PLATFORM',
                'sort_order' => 40,
                'products' => [
                    ['name' => 'Power BI', 'skus' => [
                        ['code' => 'PBI-PRO'],
                        ['code' => 'PBI-PREMIUM-PER-USER'],
                        ['code' => 'PBI-PREMIUM-PER-CAPACITY', 'billing_model' => 'CONSUMPTION', 'consumption_unit' => 'v-core-month'],
                    ]],
                    ['name' => 'Power Apps', 'skus' => [
                        ['code' => 'PAPPS-PREMIUM'], ['code' => 'PAPPS-PER-APP'],
                    ]],
                    ['name' => 'Power Automate', 'skus' => [
                        ['code' => 'PAUTO-PREMIUM'],
                        ['code' => 'PAUTO-PROCESS-ATTENDED'],
                        ['code' => 'PAUTO-PROCESS-UNATTENDED'],
                    ]],
                    ['name' => 'Copilot Studio', 'skus' => [['code' => 'COPILOT-STUDIO']]],
                ],
            ],

            // ---------------------------------------------------------------
            // Windows Server & SQL Server
            // ---------------------------------------------------------------
            [
                'code' => 'WINSVR',
                'name' => 'Windows Server & SQL Server',
                'product_line' => 'WINDOWS_SERVER',
                'sort_order' => 50,
                'products' => [
                    ['name' => 'Windows Server', 'licensing_program' => 'PERPETUAL', 'skus' => [
                        ['code' => 'WINSVR-STD-2CORE', 'billing_model' => 'ONE_TIME', 'term' => 'NA'],
                        ['code' => 'WINSVR-DC-2CORE', 'billing_model' => 'ONE_TIME', 'term' => 'NA'],
                        ['code' => 'WINSVR-CAL', 'billing_model' => 'ONE_TIME', 'term' => 'NA'],
                    ]],
                    ['name' => 'SQL Server', 'licensing_program' => 'PERPETUAL', 'skus' => [
                        ['code' => 'SQLSVR-STD-CORE', 'billing_model' => 'ONE_TIME', 'term' => 'NA'],
                        ['code' => 'SQLSVR-ENT-CORE', 'billing_model' => 'ONE_TIME', 'term' => 'NA'],
                        ['code' => 'SQLSVR-CAL', 'billing_model' => 'ONE_TIME', 'term' => 'NA'],
                    ]],
                ],
            ],

            // ---------------------------------------------------------------
            // Enterprise Mobility + Security
            // ---------------------------------------------------------------
            [
                'code' => 'EMS',
                'name' => 'Enterprise Mobility + Security',
                'product_line' => 'EMS',
                'sort_order' => 60,
                'products' => [
                    ['name' => 'Enterprise Mobility + Security', 'skus' => [['code' => 'EMS-E3'], ['code' => 'EMS-E5']]],
                    ['name' => 'Microsoft Intune', 'skus' => [['code' => 'INTUNE-P1']]],
                    ['name' => 'Microsoft Defender for Endpoint', 'skus' => [['code' => 'DEFENDER-ENDPOINT-P1'], ['code' => 'DEFENDER-ENDPOINT-P2']]],
                    ['name' => 'Microsoft Defender for Office 365', 'skus' => [['code' => 'DEFENDER-O365-P1'], ['code' => 'DEFENDER-O365-P2']]],
                    ['name' => 'Microsoft Purview', 'skus' => [['code' => 'PURVIEW-INFOPROTECTION'], ['code' => 'PURVIEW-DLP']]],
                    ['name' => 'Microsoft Defender for Identity', 'skus' => [['code' => 'DEFENDER-IDENTITY']]],
                ],
            ],
        ];
    }

    /**
     * Helper for Azure-style consumption SKUs that all share one unit.
     */
    private function consumptionSkus(array $codes, string $unit): array
    {
        return array_map(fn (string $code) => [
            'code' => $code,
            'billing_model' => 'CONSUMPTION',
            'consumption_unit' => $unit,
            'term' => 'NA',
            'billing_cycle_options' => ['monthly'],
        ], $codes);
    }
}
