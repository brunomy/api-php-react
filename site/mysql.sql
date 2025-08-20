ALTER TABLE tb_pedidos_pedidos 
ADD COLUMN id_empresa_faturado INT AFTER id_cliente, 
ADD CONSTRAINT fk_pedidos_empresas 
FOREIGN KEY (id_empresa_faturado) 
REFERENCES tb_admin_empresas(id);

