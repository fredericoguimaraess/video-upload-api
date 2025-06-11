# Video Upload System

## Descrição
Sistema de upload e processamento de vídeos desenvolvido em Laravel que inclui:
- Upload de arquivos via endpoint REST
- Armazenamento em cloud (S3/MinIO)
- Extração de metadados com FFmpeg
- Comunicação assíncrona via filas

# 🚀 Sobre a aplicação

## Pré-requisitos
- PHP 8.4.1+
- Composer 2.0+
- MySQL 8.0+
- FFmpeg 4.0+
- Serviço S3 ou MinIO
- (Opcional) RabbitMQ/Redis para filas

## Instalação
# Clone o repositório

## Instale as dependências
composer install

## Configure o ambiente


## 🏗️ Arquitetura e Padrões de Design

### Repository Pattern com Interfaces
A implementação do **Repository Pattern** com interfaces proporciona:

1. **Desacoplamento**:
   - Separação clara entre camada de negócios (Services) e acesso a dados (Repositories)
   - Exemplo: `VideoRepositoryInterface` define o contrato que qualquer implementação deve seguir

2. **Testabilidade**:
   - Facilita a criação de mocks para testes unitários
   - Permite substituir a implementação real por in-memory repositories durante testes

3. **Flexibilidade**:
   - Troca de mecanismos de persistência sem afetar a lógica de negócios
   - Possibilidade de implementar diferentes estratégias (MySQL, MongoDB, APIs externas)

4. **Manutenibilidade**:
   - Centralização das operações de banco de dados
   - Fácil localização e modificação de queries

### Camada de Services
A divisão em serviços (`VideoService`, `S3Service`, etc.) oferece:

- **Separação de preocupações**:
  - `VideoService`: Orquestração do fluxo principal
  - `S3Service`: Lógica específica de armazenamento
  - `VideoProcessorService`: Processamento de vídeos com FFmpeg

- **Reusabilidade**:
  - Lógica compartilhável entre diferentes partes da aplicação
  - Exemplo: `S3Service` pode ser utilizado por outros módulos

- **Single Responsibility**:
  - Cada serviço tem uma única responsabilidade clara
  - Facilita a identificação e correção de bugs

### Fluxo Tipico
1. **Controller**:
   - Recebe requisição HTTP
   - Delega para Services
   - Retorna respostas formatadas

2. **Service**:
   - Orquestra a lógica de negócios
   - Utiliza Repositories para persistência
   - Chama serviços especializados (S3, FFmpeg)

3. **Repository**:
   - Implementa operações de banco de dados
   - Traduz entre objetos PHP e armazenamento

```diff
+ Benefício Principal: Alterações em qualquer camada têm impacto mínimo nas outras
- Sem esse padrão: Modificações no banco poderiam exigir mudanças em múltiplos controllers

